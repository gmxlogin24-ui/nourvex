<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_revenue', function (Blueprint $table) {
            $table->id();
            // marketplace, gigs, vtu
            $table->string('source');
            $table->decimal('amount', 12, 2);
            $table->string('description');
            // polymorphic-lite: e.g. 'listing'/123, 'gig'/45, 'service_purchase'/9
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->timestamps();

            $table->index(['source', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_revenue');
    }
};
