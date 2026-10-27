<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_user_id')->index();
            $table->unsignedBigInteger('viewer_id')->index();
            $table->date('viewed_on');
            $table->timestamps();
            $table->unique(['profile_user_id', 'viewer_id', 'viewed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_views');
    }
};
