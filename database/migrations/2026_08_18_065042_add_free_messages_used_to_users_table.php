<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Unpaid users get 1 free message (gigs or marketplace, combined) before
            // being pushed to pay the signup fee. Tracks how many they've used.
            $table->unsignedTinyInteger('free_messages_used')->default(0)->after('signup_fee_paid');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('free_messages_used');
        });
    }
};
