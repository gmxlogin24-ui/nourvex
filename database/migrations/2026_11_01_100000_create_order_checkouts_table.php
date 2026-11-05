<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_checkouts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('tx_ref', 80)->unique();
            $t->decimal('amount', 12, 2);
            $t->string('kind', 30);
            $t->string('target_id', 64)->nullable();
            $t->string('status', 20)->default('pending');
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_checkouts');
    }
};
