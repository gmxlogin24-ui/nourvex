<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('expenses')) {
            return;
        }
        Schema::create('expenses', function (Blueprint $t) {
            $t->id();
            $t->string('category', 40);
            $t->decimal('amount', 14, 2);
            $t->string('description')->nullable();
            $t->date('incurred_on');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->index('incurred_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
