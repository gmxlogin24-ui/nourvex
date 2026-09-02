<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'business' (user-to-user), 'concierge' (user-to-AI)
            $table->string('subject_type')->nullable(); // e.g. App\Models\Gig, App\Models\Listing - future migration target
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('title')->nullable(); // e.g. business name, or auto-generated
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['type', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
