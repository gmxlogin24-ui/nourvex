<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('body', 300);
            $t->timestamps();
            $t->index(['news_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_comments');
    }
};
