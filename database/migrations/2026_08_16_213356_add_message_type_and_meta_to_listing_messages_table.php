<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_messages', function (Blueprint $table) {
            // text: normal chat message. meetup_proposal: structured card with
            // accept/decline. Extendable later without a new table.
            $table->string('message_type')->default('text')->after('body');
            // { when: string, where: string, status: pending|accepted|declined,
            //   proposed_by: user_id }
            $table->json('meta')->nullable()->after('message_type');
        });
    }

    public function down(): void
    {
        Schema::table('listing_messages', function (Blueprint $table) {
            $table->dropColumn(['message_type', 'meta']);
        });
    }
};
