<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('investor_profiles')) {
            Schema::create('investor_profiles', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $t->unsignedBigInteger('annual_income_naira')->default(0);
                $t->timestamp('declared_at')->nullable();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('investment_diligence')) {
            Schema::create('investment_diligence', function (Blueprint $t) {
                $t->id();
                $t->foreignId('investment_project_id')->unique()->constrained()->cascadeOnDelete();
                $t->json('checks')->nullable();
                $t->string('risk_rating', 10)->nullable();
                $t->string('location_state', 100)->nullable();
                $t->string('location_address', 255)->nullable();
                $t->string('cac_number', 40)->nullable();
                $t->text('use_of_funds')->nullable();
                $t->text('risk_disclosure')->nullable();
                $t->text('notes')->nullable();
                $t->unsignedBigInteger('updated_by')->nullable();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('investment_milestones')) {
            Schema::create('investment_milestones', function (Blueprint $t) {
                $t->id();
                $t->foreignId('investment_project_id')->constrained()->cascadeOnDelete();
                $t->unsignedInteger('position')->default(1);
                $t->string('title', 120);
                $t->text('description')->nullable();
                $t->unsignedBigInteger('amount_q');
                $t->string('status', 20)->default('pending');
                $t->text('proof_note')->nullable();
                $t->timestamp('released_at')->nullable();
                $t->timestamps();
                $t->index(['investment_project_id', 'position']);
            });
        }
        if (! Schema::hasTable('investment_updates')) {
            Schema::create('investment_updates', function (Blueprint $t) {
                $t->id();
                $t->foreignId('investment_project_id')->constrained()->cascadeOnDelete();
                $t->unsignedBigInteger('author_id')->nullable();
                $t->string('title', 120);
                $t->text('body');
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('investment_audit')) {
            Schema::create('investment_audit', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('admin_id')->nullable();
                $t->string('action', 60);
                $t->string('subject_type', 40)->nullable();
                $t->unsignedBigInteger('subject_id')->nullable();
                $t->text('details')->nullable();
                $t->string('ip', 45)->nullable();
                $t->timestamp('created_at')->nullable();
            });
        }
        if (! Schema::hasColumn('investments', 'risk_ack_at')) {
            Schema::table('investments', function (Blueprint $t) {
                $t->string('risk_ack_version', 40)->nullable();
                $t->timestamp('risk_ack_at')->nullable();
                $t->string('risk_ack_ip', 45)->nullable();
                $t->timestamp('cooling_off_until')->nullable();
                $t->timestamp('cancelled_at')->nullable();
            });
        }
        if (Schema::hasTable('kyc_profiles') && ! Schema::hasColumn('kyc_profiles', 'reviewed_by')) {
            Schema::table('kyc_profiles', function (Blueprint $t) {
                $t->unsignedBigInteger('reviewed_by')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_audit');
        Schema::dropIfExists('investment_updates');
        Schema::dropIfExists('investment_milestones');
        Schema::dropIfExists('investment_diligence');
        Schema::dropIfExists('investor_profiles');
    }
};
