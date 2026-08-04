<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gigs', function (Blueprint $table) {
            $table->string('guest_name')->nullable()->after('accepted_by');
            $table->string('guest_token')->nullable()->index()->after('guest_name');
        });
    }

    public function down(): void
    {
        Schema::table('gigs', function (Blueprint $table) {
            $table->dropColumn(['guest_name', 'guest_token']);
        });
    }
};