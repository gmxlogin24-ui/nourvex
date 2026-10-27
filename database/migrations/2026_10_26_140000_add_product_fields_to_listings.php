<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (! Schema::hasColumn('listings', 'old_price')) { $table->decimal('old_price', 12, 2)->nullable(); }
            if (! Schema::hasColumn('listings', 'colors')) { $table->string('colors', 255)->nullable(); }
            if (! Schema::hasColumn('listings', 'sizes')) { $table->string('sizes', 255)->nullable(); }
            if (! Schema::hasColumn('listings', 'specs')) { $table->text('specs')->nullable(); }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['old_price', 'colors', 'sizes', 'specs']);
        });
    }
};
