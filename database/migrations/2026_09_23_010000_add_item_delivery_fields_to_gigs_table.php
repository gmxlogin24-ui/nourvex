<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gigs', function (Blueprint $table) {
            $table->boolean('has_item')->default(false)->after('delivery_attachment_path');
            $table->string('item_name')->nullable()->after('has_item');
            $table->unsignedInteger('item_qty')->nullable()->after('item_name');
            $table->decimal('item_cost', 10, 2)->nullable()->after('item_qty');
            $table->string('delivery_address')->nullable()->after('item_cost');
            $table->string('item_delivery_method')->nullable()->after('delivery_address'); // meetup, ship
        });
    }

    public function down(): void
    {
        Schema::table('gigs', function (Blueprint $table) {
            $table->dropColumn(['has_item', 'item_name', 'item_qty', 'item_cost', 'delivery_address', 'item_delivery_method']);
        });
    }
};
