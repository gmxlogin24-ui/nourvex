<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_purchases', function (Blueprint $table) {
            $table->string('idempotency_key')->nullable()->unique()->after('provider_reference');
        });
    }

    public function down(): void
    {
        Schema::table('service_purchases', function (Blueprint $table) {
            $table->dropColumn('idempotency_key');
        });
    }
};
