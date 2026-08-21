<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Flutterwave's reusable charge token - never store raw card numbers/CVV.
            $table->string('card_token')->nullable()->after('badge_tier');
            $table->string('card_last4', 4)->nullable()->after('card_token');
            $table->string('card_type')->nullable()->after('card_last4'); // visa, mastercard, verve
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['card_token', 'card_last4', 'card_type']);
        });
    }
};
