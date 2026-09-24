<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gigs', function (Blueprint $table) {
            $table->date('deadline')->nullable()->after('description');
            $table->string('location_type')->nullable()->after('deadline'); // remote, onsite, pickup
            $table->string('location')->nullable()->after('location_type');
            $table->json('details')->nullable()->after('location');
            $table->string('attachment_path')->nullable()->after('details');
            $table->string('delivery_attachment_path')->nullable()->after('delivery_note');
        });
    }

    public function down(): void
    {
        Schema::table('gigs', function (Blueprint $table) {
            $table->dropColumn(['deadline', 'location_type', 'location', 'details', 'attachment_path', 'delivery_attachment_path']);
        });
    }
};
