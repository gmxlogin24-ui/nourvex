<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'navbar_style')) $table->string('navbar_style')->nullable()->after('theme_style');
            if (!Schema::hasColumn('users', 'topbar_style')) $table->string('topbar_style')->nullable()->after('navbar_style');
            if (!Schema::hasColumn('users', 'notif_style')) $table->string('notif_style')->nullable()->after('topbar_style');
            if (!Schema::hasColumn('users', 'sidebar_style')) $table->string('sidebar_style')->nullable()->after('notif_style');
            if (!Schema::hasColumn('users', 'card_style')) $table->string('card_style')->nullable()->after('sidebar_style');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['navbar_style','topbar_style','notif_style','sidebar_style','card_style']);
        });
    }
};
