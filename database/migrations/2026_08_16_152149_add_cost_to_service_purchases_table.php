<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_purchases', function (Blueprint $table) {
            // Wholesale price the provider actually charges us, known server-side
            // BEFORE the provider call (from the plan catalog). Null/equal-to-amount
            // for airtime, which has no markup. amount - cost = platform margin.
            $table->decimal('cost', 10, 2)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('service_purchases', function (Blueprint $table) {
            $table->dropColumn('cost');
        });
    }
};
