<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('follows')) {
            Schema::create('follows', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('follower_id');
                $t->unsignedBigInteger('followee_id');
                $t->timestamps();
                $t->unique(['follower_id', 'followee_id']);
            });
        }
        if (!Schema::hasTable('saved_replies')) {
            Schema::create('saved_replies', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->string('body', 500);
                $t->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('follows');
        Schema::dropIfExists('saved_replies');
    }
};
