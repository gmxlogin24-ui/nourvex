<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('admin_audit_logs')) {
            Schema::create('admin_audit_logs', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->nullable()->index();
                $t->string('method', 8);
                $t->string('path', 255);
                $t->string('ip', 64)->nullable();
                $t->string('agent', 255)->nullable();
                $t->text('payload')->nullable();
                $t->unsignedSmallInteger('status')->default(0);
                $t->timestamp('created_at')->useCurrent();
            });
        }
    }
    public function down(): void { Schema::dropIfExists('admin_audit_logs'); }
};
