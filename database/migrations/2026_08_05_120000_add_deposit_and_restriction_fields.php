<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gigs', function (Blueprint $table) {
            // How much has actually been pulled from the poster's wallet so far.
            // Set to 50% of amount at posting, kept in sync by update(), and closed
            // out to the full amount when complete() charges the remaining balance.
            // Tracking this explicitly (rather than assuming it's always exactly
            // half of `amount`) keeps things correct even if the poster edits the
            // price mid-flight.
            $table->decimal('escrowed_amount', 10, 2)->default(0)->after('amount');

            // Set once a "poster hasn't confirmed" reminder has been sent for a
            // delivered gig, so the reminder job doesn't re-notify on every run.
            $table->timestamp('payment_reminder_sent_at')->nullable()->after('delivered_at');
        });

        Schema::table('users', function (Blueprint $table) {
            // Separate from is_banned on purpose: this means "owes the platform
            // money from a resolved dispute," not "kicked off for misconduct."
            // An admin can lift it independently of any ban once the balance
            // is cleared.
            $table->boolean('wallet_restricted')->default(false)->after('is_banned');
        });
    }

    public function down(): void
    {
        Schema::table('gigs', function (Blueprint $table) {
            $table->dropColumn(['escrowed_amount', 'payment_reminder_sent_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wallet_restricted');
        });
    }
};
