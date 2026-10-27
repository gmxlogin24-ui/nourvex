<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->date('viewed_on');
            $table->timestamps();
            $table->unique(['listing_id', 'user_id', 'viewed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_views');
    }
};
