<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_comment_likes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('comment_id')->index();
            $t->unsignedBigInteger('user_id');
            $t->timestamps();
            $t->unique(['comment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_comment_likes');
    }
};
