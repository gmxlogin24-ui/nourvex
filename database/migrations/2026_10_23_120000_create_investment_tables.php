<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->text('details')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('investment_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20); // business, land
            $table->string('title', 120);
            $table->text('description');
            $table->unsignedBigInteger('goal_q');
            $table->unsignedBigInteger('min_invest_q');
            $table->unsignedBigInteger('max_invest_q');
            $table->unsignedBigInteger('raised_q')->default(0);            // escrow in
            $table->unsignedBigInteger('released_q')->default(0);          // escrow out to owner
            $table->unsignedBigInteger('returns_reported_q')->default(0);  // admin-declared income
            $table->unsignedBigInteger('returns_distributed_q')->default(0);
            $table->string('status', 20)->default('pending_review'); // pending_review, open, funded, cancelled
            $table->timestamp('closes_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'type']);
        });

        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('investment_project_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount_q');
            $table->string('status', 20)->default('active'); // active, refunded
            $table->string('idempotency_key', 64)->unique();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['investment_project_id', 'status']);
        });

        Schema::create('investment_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('investment_project_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20); // return, refund
            $table->unsignedBigInteger('amount_q');
            $table->string('reference', 120)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_payouts');
        Schema::dropIfExists('investments');
        Schema::dropIfExists('investment_projects');
        Schema::dropIfExists('investment_verifications');
    }
};
