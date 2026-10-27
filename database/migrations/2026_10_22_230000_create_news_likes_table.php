<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_likes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['news_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_likes');
    }
};
