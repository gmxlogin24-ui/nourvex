<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('conversation_messages', function (Blueprint $t) {
            if (!Schema::hasColumn('conversation_messages', 'reply_to_id')) $t->unsignedBigInteger('reply_to_id')->nullable();
            if (!Schema::hasColumn('conversation_messages', 'edited_at')) $t->timestamp('edited_at')->nullable();
            if (!Schema::hasColumn('conversation_messages', 'deleted_at')) $t->timestamp('deleted_at')->nullable();
        });
        Schema::table('conversation_participants', function (Blueprint $t) {
            if (!Schema::hasColumn('conversation_participants', 'typing_at')) $t->timestamp('typing_at')->nullable();
        });
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'last_seen_at')) $t->timestamp('last_seen_at')->nullable();
        });
        if (!Schema::hasTable('message_reactions')) {
            Schema::create('message_reactions', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('message_id')->index();
                $t->unsignedBigInteger('user_id');
                $t->string('emoji', 16);
                $t->timestamps();
                $t->unique(['message_id', 'user_id']);
            });
        }
    }
    public function down(): void { Schema::dropIfExists('message_reactions'); }
};
