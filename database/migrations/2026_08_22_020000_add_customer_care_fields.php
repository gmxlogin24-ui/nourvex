<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_orders', function (Blueprint $table) {
            $table->string('tracking_id')->nullable();
            $table->timestamp('delivery_confirmed_at')->nullable();
            $table->foreignId('delivery_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('gig_disputes', function (Blueprint $table) {
            $table->text('cc_notes')->nullable();
            $table->boolean('escalated')->default(false);
        });

        Schema::table('listing_disputes', function (Blueprint $table) {
            $table->text('cc_notes')->nullable();
            $table->boolean('escalated')->default(false);
        });

        Schema::create('flags', function (Blueprint $table) {
            $table->id();
            $table->string('flaggable_type');
            $table->unsignedBigInteger('flaggable_id');
            $table->text('reason');
            $table->foreignId('raised_by')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('open'); // open | reviewed | dismissed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('listing_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_confirmed_by');
            $table->dropColumn(['tracking_id', 'delivery_confirmed_at']);
        });
        Schema::table('gig_disputes', function (Blueprint $table) {
            $table->dropColumn(['cc_notes', 'escalated']);
        });
        Schema::table('listing_disputes', function (Blueprint $table) {
            $table->dropColumn(['cc_notes', 'escalated']);
        });
        Schema::dropIfExists('flags');
    }
};
