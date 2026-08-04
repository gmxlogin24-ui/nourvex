<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('service_type');
            $table->string('network');
            $table->string('phone');
            $table->string('plan_code')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');
            $table->string('provider_reference')->unique();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('service_purchases');
    }
};
