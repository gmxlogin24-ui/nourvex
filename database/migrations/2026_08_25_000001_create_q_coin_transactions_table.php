<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('q_coin_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Signed - earning is positive, redemption/spend is negative. Kept
            // entirely separate from the real-money `transactions` table so
            // Q Coin activity can never be summed into Naira totalIn/totalOut.
            $table->integer('amount');
            $table->string('type'); // e.g. daily_claim, task_reward, referral, redemption
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('q_coin_transactions');
    }
};
