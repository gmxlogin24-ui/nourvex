<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username', 20)->nullable()->unique();
            });
        }
        $taken = [];
        foreach (DB::table('users')->orderBy('id')->get(['id', 'name', 'username']) as $u) {
            if ($u->username) { $taken[$u->username] = true; continue; }
            $base = substr(preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace(' ', '_', (string) $u->name))), 0, 16);
            if (strlen($base) < 3) { $base = 'user' . $u->id; }
            $name = $base; $n = 2;
            while (isset($taken[$name])) { $name = substr($base, 0, 16) . $n++; }
            $taken[$name] = true;
            DB::table('users')->where('id', $u->id)->update(['username' => $name]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
