<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_style')->default('plain')->change();
        });
        \App\Models\User::whereNull('theme_style')->update(['theme_style' => 'plain']);
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_style')->default('glass')->change();
        });
    }
};
