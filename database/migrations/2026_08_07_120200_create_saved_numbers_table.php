<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'phone' or 'smart_card'
            $table->string('provider')->nullable(); // network or cable provider, if known
            $table->string('number');
            $table->string('label')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'type', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_numbers');
    }
};
