<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('news_comments', 'parent_id')) {
            Schema::table('news_comments', function (Blueprint $t) {
                $t->unsignedBigInteger('parent_id')->nullable()->index()->after('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('news_comments', function (Blueprint $t) {
            $t->dropColumn('parent_id');
        });
    }
};
