<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan'); // tier1, tier2
            $table->unsignedInteger('amount');
            $table->string('payment_method'); // wallet, flutterwave
            $table->string('flutterwave_tx_ref')->nullable();
            $table->string('status')->default('active'); // active, expired, cancelled
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('subscriptions');
    }
};
