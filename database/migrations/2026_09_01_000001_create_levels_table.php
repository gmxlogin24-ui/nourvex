<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('level_number')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->default('star');
            $table->string('color')->default('blue');
            $table->unsignedBigInteger('min_q_coins')->default(0);
            $table->unsignedInteger('min_gigs_completed')->default(0);
            $table->unsignedInteger('min_sales')->default(0);
            $table->unsignedInteger('min_referrals')->default(0);
            $table->unsignedInteger('min_account_age_days')->default(0);
            $table->boolean('requires_verified')->default(false);
            $table->json('perks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
