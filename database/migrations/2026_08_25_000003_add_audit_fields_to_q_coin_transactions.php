<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('q_coin_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('balance_after')->after('amount');
            $table->string('reference_id')->nullable()->after('description');
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('q_coin_transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'type']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropColumn(['balance_after', 'reference_id']);
        });
    }
};
