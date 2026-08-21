<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raised_by')->constrained('users')->cascadeOnDelete();
            $table->text('description');
            // open: awaiting admin review
            // resolved_paid: admin released escrow to seller
            // resolved_refunded: admin refunded the buyer
            // dismissed: admin closed with no action (rare — usually paid/refunded)
            $table->string('status')->default('open');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // one open dispute per listing at a time
            $table->unique('listing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_disputes');
    }
};
