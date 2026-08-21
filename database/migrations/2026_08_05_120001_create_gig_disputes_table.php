<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gig_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gig_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raised_by')->constrained('users')->cascadeOnDelete();
            $table->text('description');
            // open: awaiting admin review
            // resolved_paid: admin sided with worker, remaining balance force-paid
            // dismissed: admin sided with poster, no payout
            $table->string('status')->default('open');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // One open dispute per gig at a time — matches the Fiverr pattern of
            // withdrawing an existing request before opening a new one.
            $table->unique('gig_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gig_disputes');
    }
};
