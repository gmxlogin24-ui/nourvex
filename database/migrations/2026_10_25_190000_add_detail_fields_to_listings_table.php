<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'brand')) { $table->string('brand')->nullable(); }
            if (!Schema::hasColumn('listings', 'highlights')) { $table->text('highlights')->nullable(); }
            if (!Schema::hasColumn('listings', 'whats_in_box')) { $table->text('whats_in_box')->nullable(); }
            if (!Schema::hasColumn('listings', 'warranty')) { $table->string('warranty')->nullable(); }
            if (!Schema::hasColumn('listings', 'return_policy')) { $table->text('return_policy')->nullable(); }
            if (!Schema::hasColumn('listings', 'negotiable')) { $table->boolean('negotiable')->default(false); }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['brand', 'highlights', 'whats_in_box', 'warranty', 'return_policy', 'negotiable']);
        });
    }
};
