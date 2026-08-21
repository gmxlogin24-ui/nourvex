<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            // meetup: on-campus handoff. ship: seller sends it, buyer may not be in town.
            $table->string('delivery_method')->default('meetup')->after('listing_type');
            $table->text('shipping_note')->nullable()->after('delivery_method');
            $table->timestamp('shipped_at')->nullable()->after('shipping_note');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['delivery_method', 'shipping_note', 'shipped_at']);
        });
    }
};
