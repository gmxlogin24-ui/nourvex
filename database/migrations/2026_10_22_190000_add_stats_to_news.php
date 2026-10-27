<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('news', function (Blueprint $t) {
            if (!Schema::hasColumn('news', 'views'))  $t->unsignedInteger('views')->default(0);
            if (!Schema::hasColumn('news', 'clicks')) $t->unsignedInteger('clicks')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $t) {
            $t->dropColumn(['views', 'clicks']);
        });
    }
};
