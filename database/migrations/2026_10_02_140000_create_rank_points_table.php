<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rank_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source', 40);
            $table->integer('points');
            $table->string('ref', 100)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'source', 'ref']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rank_points');
    }
};
