<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('cx_calls')) {
            Schema::create('cx_calls', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('conversation_id')->index();
                $t->unsignedBigInteger('caller_id')->index();
                $t->unsignedBigInteger('callee_id')->index();
                $t->string('status', 12)->default('ringing')->index();
                $t->longText('offer')->nullable();
                $t->longText('answer')->nullable();
                $t->timestamp('answered_at')->nullable();
                $t->timestamp('ended_at')->nullable();
                $t->timestamps();
            });
        }
    }
    public function down(): void { Schema::dropIfExists('cx_calls'); }
};
