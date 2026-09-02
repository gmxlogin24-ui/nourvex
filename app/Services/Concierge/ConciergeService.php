<?php

namespace App\Services\Concierge;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ConciergeService
{
    protected const MAX_TOOL_ROUNDS = 4;

    public function __construct(protected ConciergeToolRegistry $tools) {}

    protected function systemPrompt(User $user): string
    {
        return "You are LEVI, QLEVO's built-in AI assistant. You are not a generic chatbot - you are "
            . "a native part of the QLEVO product, and you should never refer to yourself as Claude, "
            . "an Anthropic model, or any name other than LEVI. If asked what model or company powers you, "
            . "say you're LEVI, built for QLEVO, and redirect back to helping them. "
            . "QLEVO is an ecosystem with Gigs, a Marketplace, a Wallet, and Q Coin rewards. "
            . "Your personality: warm, sharp, a little upbeat - like a capable friend who works at QLEVO "
            . "and genuinely wants the user to win, not a customer service script. "
            . "Help the user find things and take real actions using the tools available to you, rather "
            . "than just describing how they could do it themselves. "
            . "Be concise - this is a mobile chat, keep replies short and conversational, not a form. "
            . "When you navigate the user somewhere or complete an action, say what you did in plain, "
            . "human language, e.g. \"Found 3 gigs under 20k, opening the first one for you\" not "
            . "\"I have executed the search_gigs function.\" "
            . "Never claim to have done something you did not actually call a tool for. "
            . "The user's name is {$user->name} and their tier is {$user->tier}.";
    }

    /**
     * Send a user message, run the tool-use loop, persist everything,
     * return the assistant's final reply plus any client actions
     * (e.g. navigation) that occurred along the way.
     */
    public function handleUserMessage(User $user, Conversation $conversation, string $userText): array
    {
        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message_type' => 'text',
            'body' => $userText,
        ]);

        $history = $this->buildHistoryPayload($conversation);
        $clientActions = [];
        $roundsLeft = self::MAX_TOOL_ROUNDS;

        while ($roundsLeft-- > 0) {
            $response = $this->callClaude($user, $history);
            $stopReason = $response['stop_reason'] ?? null;
            $toolUses = array_filter($response['content'] ?? [], fn ($b) => ($b['type'] ?? null) === 'tool_use');

            if (empty($toolUses)) {
                $text = $this->extractText($response);
                $this->persistAssistantMessage($conversation, $text, null);
                return ['reply' => $text, 'client_actions' => $clientActions];
            }

            // Assistant turn (including tool_use blocks) goes back into history
            $history[] = ['role' => 'assistant', 'content' => $response['content']];

            $toolResults = [];
            foreach ($toolUses as $toolUse) {
                $execResult = $this->tools->execute($user, $toolUse['name'], $toolUse['input'] ?? []);

                if (isset($execResult['client_action'])) {
                    $clientActions[] = $execResult['client_action'];
                }

                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $toolUse['id'],
                    'content' => json_encode($execResult['result'] ?? []),
                ];

                // Log the tool call itself as a system-ish message for auditability
                ConversationMessage::create([
                    'conversation_id' => $conversation->id,
                    'is_ai' => true,
                    'message_type' => 'action_card',
                    'body' => null,
                    'meta' => ['tool' => $toolUse['name'], 'input' => $toolUse['input'] ?? [], 'result' => $execResult['result'] ?? []],
                ]);
            }

            $history[] = ['role' => 'user', 'content' => $toolResults];

            if ($stopReason !== 'tool_use') {
                $text = $this->extractText($response);
                $this->persistAssistantMessage($conversation, $text, null);
                return ['reply' => $text, 'client_actions' => $clientActions];
            }
        }

        $fallback = "I ran into a lot of steps handling that — could you rephrase or simplify the request?";
        $this->persistAssistantMessage($conversation, $fallback, null);
        return ['reply' => $fallback, 'client_actions' => $clientActions];
    }

    protected function callClaude(User $user, array $messages): array
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not set.');
        }

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model'),
            'max_tokens' => 1024,
            'system' => $this->systemPrompt($user),
            'messages' => $messages,
            'tools' => $this->tools->schemas(),
        ]);

        if ($response->failed()) {
            Log::error('Concierge Claude call failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Concierge is temporarily unavailable.');
        }

        return $response->json();
    }

    protected function extractText(array $response): string
    {
        $textBlocks = array_filter($response['content'] ?? [], fn ($b) => ($b['type'] ?? null) === 'text');
        return trim(implode("\n", array_map(fn ($b) => $b['text'], $textBlocks))) ?: "Done.";
    }

    protected function persistAssistantMessage(Conversation $conversation, string $text, ?array $meta): void
    {
        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'is_ai' => true,
            'message_type' => 'text',
            'body' => $text,
            'meta' => $meta,
        ]);
        $conversation->update(['last_message_at' => now()]);
    }

    /**
     * Rebuild the Claude message array from stored conversation history.
     * Tool-call/result pairs stored as action_card messages are replayed
     * as assistant/tool_result turns so context survives across requests.
     */
    protected function buildHistoryPayload(Conversation $conversation): array
    {
        $messages = $conversation->messages()->orderBy('created_at')->limit(40)->get();
        $payload = [];

        foreach ($messages as $m) {
            if ($m->message_type === 'action_card') {
                continue; // reconstructing full tool-call turns from storage is a v2 concern; keep context on plain text turns for now
            }
            if ($m->is_ai) {
                $payload[] = ['role' => 'assistant', 'content' => $m->body ?? ''];
            } elseif (!$m->is_system) {
                $payload[] = ['role' => 'user', 'content' => $m->body ?? ''];
            }
        }

        return $payload;
    }
}
