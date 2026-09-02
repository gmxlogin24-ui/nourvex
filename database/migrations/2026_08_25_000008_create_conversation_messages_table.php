<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete(); // null = AI/system message
            $table->boolean('is_ai')->default(false);
            $table->boolean('is_system')->default(false);
            $table->string('message_type')->default('text'); // text, action_card, system, voice_transcript
            $table->text('body')->nullable();
            $table->json('meta')->nullable(); // e.g. { tool_call: 'search_gigs', tool_result: {...} } for concierge messages
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
    }
};
