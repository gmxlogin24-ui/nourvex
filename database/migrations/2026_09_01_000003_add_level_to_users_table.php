<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'current_level_id')) {
                $table->foreignId('current_level_id')->nullable()->after('q_coin_streak_days');
            }
            if (! Schema::hasColumn('users', 'level_achieved_at')) {
                $table->timestamp('level_achieved_at')->nullable()->after('current_level_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'current_level_id')) {
                $table->dropColumn('current_level_id');
            }
            if (Schema::hasColumn('users', 'level_achieved_at')) {
                $table->dropColumn('level_achieved_at');
            }
        });
    }
};
