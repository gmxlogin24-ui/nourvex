<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('area', 30)->nullable()->after('spoken_language')->index();
            $table->timestamp('area_verified_at')->nullable()->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['area']);
            $table->dropColumn(['area', 'area_verified_at']);
        });
    }
};
