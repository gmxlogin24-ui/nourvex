<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('badge_tier')->nullable()->after('is_verified_seller'); // null, 'bronze' (paid signup badge)
            $table->boolean('signup_fee_paid')->default(false)->after('badge_tier');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['badge_tier', 'signup_fee_paid']);
        });
    }
};