<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Schema::getTableListing() as $table) {
            if (stripos($table, 'flutterwave') !== false) {
                $newTable = str_ireplace('flutterwave', 'paystack', $table);
                if (! Schema::hasTable($newTable)) {
                    Schema::rename($table, $newTable);
                }
            }
        }

        foreach (Schema::getTableListing() as $table) {
            foreach (Schema::getColumnListing($table) as $column) {
                if (stripos($column, 'flutterwave') === false) {
                    continue;
                }
                $new = str_ireplace('flutterwave', 'paystack', $column);
                if (Schema::hasColumn($table, $new)) {
                    continue;
                }
                Schema::table($table, function (Blueprint $t) use ($column, $new) {
                    $t->renameColumn($column, $new);
                });
            }
        }
    }

    public function down(): void
    {
    }
};
