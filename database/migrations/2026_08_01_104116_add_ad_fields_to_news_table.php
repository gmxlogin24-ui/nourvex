<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('published_at'); // null = permanent (admin news); set = self-serve ad duration
            $table->boolean('is_featured')->default(false)->after('expires_at'); // Premium (tier3) ads pinned at top
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'is_featured']);
        });
    }
};