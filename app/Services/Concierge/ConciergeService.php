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
    protected const MAX_RETRIES = 3;

    public function __construct(protected ConciergeToolRegistry $tools) {}

    protected function systemPrompt(User $user): string
    {
        return "You are LEVI, QLEVO's built-in AI assistant. You are not a generic chatbot - you are "
            . "a native part of the QLEVO product, and you should never refer to yourself as Gemini, "
            . "a Google model, or any name other than LEVI. If asked what model or company powers you, "
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
            $response = $this->callGemini($user, $history);
            $parts = $response['candidates'][0]['content']['parts'] ?? [];
            $functionCalls = array_filter($parts, fn ($p) => isset($p['functionCall']));

            if (empty($functionCalls)) {
                $text = $this->extractText($parts);
                $this->persistAssistantMessage($conversation, $text, null);
                return ['reply' => $text, 'client_actions' => $clientActions];
            }

            $history[] = ['role' => 'model', 'parts' => $parts];

            $responseParts = [];
            foreach ($functionCalls as $call) {
                $name = $call['functionCall']['name'] ?? '';
                $args = $call['functionCall']['args'] ?? [];
                $execResult = $this->tools->execute($user, $name, $args);

                if (isset($execResult['client_action'])) {
                    $clientActions[] = $execResult['client_action'];
                }

                $responseParts[] = [
                    'functionResponse' => [
                        'name' => $name,
                        'response' => $execResult['result'] ?? [],
                    ],
                ];

                ConversationMessage::create([
                    'conversation_id' => $conversation->id,
                    'is_ai' => true,
                    'message_type' => 'action_card',
                    'body' => null,
                    'meta' => ['tool' => $name, 'input' => $args, 'result' => $execResult['result'] ?? []],
                ]);
            }

            $history[] = ['role' => 'user', 'parts' => $responseParts];
        }

        $fallback = "I ran into a lot of steps handling that — could you rephrase or simplify the request?";
        $this->persistAssistantMessage($conversation, $fallback, null);
        return ['reply' => $fallback, 'client_actions' => $clientActions];
    }

    protected function callGemini(User $user, array $contents): array
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            throw new RuntimeException('GEMINI_API_KEY is not set.');
        }
        $model = config('services.gemini.model', 'gemini-flash-latest');

        $body = [
            'system_instruction' => ['parts' => [['text' => $this->systemPrompt($user)]]],
            'contents' => $contents,
            'tools' => [['functionDeclarations' => $this->toGeminiTools($this->tools->schemas())]],
        ];

        $attempt = 0;
        $lastError = null;

        while ($attempt < self::MAX_RETRIES) {
            $attempt++;
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $apiKey,
            ])->timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                $body
            );

            if ($response->successful()) {
                return $response->json();
            }

            $status = $response->status();
            $lastError = $response->body();

            // 503 = model overloaded, temporary - retry with backoff.
            // Anything else (bad key, bad request) - fail immediately, retrying won't help.
            if ($status !== 503) {
                break;
            }

            usleep(500000 * $attempt); // 0.5s, 1s, 1.5s
        }

        Log::error('Concierge Gemini call failed', ['error' => $lastError]);
        throw new RuntimeException('LEVI is temporarily unavailable — please try again in a moment.');
    }

    protected function toGeminiTools(array $anthropicSchemas): array
    {
        return array_map(function ($tool) {
            return [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'parameters' => $tool['input_schema'],
            ];
        }, $anthropicSchemas);
    }

    protected function extractText(array $parts): string
    {
        $texts = array_filter(array_map(fn ($p) => $p['text'] ?? null, $parts));
        return trim(implode("\n", $texts)) ?: "Done.";
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

    protected function buildHistoryPayload(Conversation $conversation): array
    {
        return $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->filter(fn ($m) => $m->message_type === 'text')
            ->map(fn ($m) => [
                'role' => $m->is_ai ? 'model' : 'user',
                'parts' => [['text' => $m->body ?? '']],
            ])
            ->values()
            ->toArray();
    }
}
