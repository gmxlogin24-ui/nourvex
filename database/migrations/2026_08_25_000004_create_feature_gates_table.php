<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_gates', function (Blueprint $table) {
            $table->id();
            $table->string('feature_key')->unique(); // e.g. 'qcoin.streak_bonus', 'concierge.voice', 'marketplace.boost'
            $table->string('label'); // human-readable, shown in admin
            $table->string('required_tier')->default('free'); // free, tier1, tier2
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_gates');
    }
};
