<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profit_share_runs', function (Blueprint $t) {
            $t->id();
            $t->string('month', 7)->unique();
            $t->decimal('revenue', 14, 2)->default(0);
            $t->decimal('costs', 14, 2)->default(0);
            $t->decimal('profit', 14, 2)->default(0);
            $t->decimal('pool', 14, 2)->default(0);
            $t->decimal('q_per_naira', 10, 2)->default(0);
            $t->unsignedBigInteger('pool_q')->default(0);
            $t->string('status', 20)->default('preview'); // preview | paid
            $t->string('mode', 20)->default('test');
            $t->timestamps();
        });

        Schema::create('profit_share_allocations', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('run_id')->index();
            $t->unsignedBigInteger('user_id')->index();
            $t->unsignedBigInteger('q_earned')->default(0);
            $t->unsignedBigInteger('q_share')->default(0);
            $t->timestamp('credited_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profit_share_allocations');
        Schema::dropIfExists('profit_share_runs');
    }
};
