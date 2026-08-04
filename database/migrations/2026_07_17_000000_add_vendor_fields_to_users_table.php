<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type')->default('guest'); // guest, vendor
            $table->string('business_name')->nullable();
            $table->string('business_logo')->nullable();
            $table->string('business_whatsapp')->nullable();
            $table->boolean('is_vendor_active')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'business_name', 'business_logo', 'business_whatsapp', 'is_vendor_active']);
        });
    }
};
