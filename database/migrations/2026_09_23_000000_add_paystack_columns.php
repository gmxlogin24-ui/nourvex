<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('paystack_customer_code')->nullable()->after('email');
            $table->string('dva_account_number')->nullable()->after('paystack_customer_code');
            $table->string('dva_bank_name')->nullable()->after('dva_account_number');
            $table->string('dva_account_id')->nullable()->after('dva_bank_name');
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'reference')) {
                $table->string('reference')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('transactions', 'status')) {
                $table->string('status')->default('successful')->after('reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['paystack_customer_code', 'dva_account_number', 'dva_bank_name', 'dva_account_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['reference', 'status']);
        });
    }
};
