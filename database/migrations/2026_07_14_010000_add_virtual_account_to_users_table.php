<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('virtual_account_number')->nullable();
            $table->string('virtual_account_bank')->nullable();
            $table->string('virtual_account_ref')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['virtual_account_number', 'virtual_account_bank', 'virtual_account_ref']);
        });
    }
};
