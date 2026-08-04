<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gig_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('gig_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'gig_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gig_views');
    }
};
