<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_url')->nullable();
            $table->string('background_url')->nullable();
            $table->string('accent_color')->default('#38D9E8');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_url', 'background_url', 'accent_color']);
        });
    }
};
