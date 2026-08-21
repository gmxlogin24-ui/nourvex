<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // 'search' or 'view'
            $table->string('category')->nullable();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->string('search_term')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
