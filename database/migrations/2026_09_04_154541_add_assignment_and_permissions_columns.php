<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gig_disputes') && !Schema::hasColumn('gig_disputes', 'assigned_to')) {
            Schema::table('gig_disputes', function (Blueprint $table) {
                $table->foreignId('assigned_to')->nullable()->after('raised_by')->constrained('users')->nullOnDelete();
                $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            });
        }

        if (Schema::hasTable('listing_disputes') && !Schema::hasColumn('listing_disputes', 'assigned_to')) {
            Schema::table('listing_disputes', function (Blueprint $table) {
                $table->foreignId('assigned_to')->nullable()->after('raised_by')->constrained('users')->nullOnDelete();
                $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            });
        }

        if (Schema::hasTable('flags') && !Schema::hasColumn('flags', 'assigned_to')) {
            Schema::table('flags', function (Blueprint $table) {
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('assigned_at')->nullable();
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'customer_care_permissions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('customer_care_permissions')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('gig_disputes', 'assigned_to')) {
            Schema::table('gig_disputes', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assigned_to');
                $table->dropColumn('assigned_at');
            });
        }
        if (Schema::hasColumn('listing_disputes', 'assigned_to')) {
            Schema::table('listing_disputes', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assigned_to');
                $table->dropColumn('assigned_at');
            });
        }
        if (Schema::hasColumn('flags', 'assigned_to')) {
            Schema::table('flags', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assigned_to');
                $table->dropColumn('assigned_at');
            });
        }
        if (Schema::hasColumn('users', 'customer_care_permissions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('customer_care_permissions');
            });
        }
    }
};
