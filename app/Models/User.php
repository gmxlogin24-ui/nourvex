<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use App\Events\TierUpgraded;

#[Fillable([
    'name', 'email', 'password', 'gender', 'spoken_language', 'avatar_url', 'background_url', 'accent_color',
    'referral_code', 'referred_by', 'wallet_balance', 'q_coin_balance', 'q_coin_last_claim_date',
    'tier', 'tier_upgraded_at', 'is_verified_seller',
    'virtual_account_number', 'virtual_account_bank', 'virtual_account_ref',
    'account_type', 'business_name', 'business_logo', 'business_whatsapp', 'is_vendor_active', 'interests',
    'streak_days', 'last_active_date', 'leaderboard_opt_in', 'q_coin_streak_days', 'wallet_restricted', 'google_id', 'transaction_pin', 'theme_style', 'mobile_nav_style', 'sidebar_style',
    'is_admin', 'role', 'level_id',
    'signup_fee_paid', 'badge_tier', 'card_token', 'card_last4', 'card_type',
    'is_banned', 'subscription_expires_at', 'skills',
    'notif_style', 'navbar_style', 'topbar_style', 'card_style',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'transaction_pin' => 'hashed',
            'interests' => 'array',
            'is_admin' => 'boolean',
            'leaderboard_opt_in' => 'boolean',
            'wallet_restricted' => 'boolean',
            'is_vendor_active' => 'boolean',
            'tier_upgraded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (User $user) {
            if ($user->wasChanged('tier')) {
                event(new TierUpgraded($user, $user->getOriginal('tier'), $user->tier));
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function completedGigsCount(): int
    {
        return \App\Models\Gig::where('accepted_by', $this->id)->where('status', 'completed')->count();
    }

    public function favoriteListings()
    {
        return $this->belongsToMany(Listing::class, 'listing_favorites')->withTimestamps();
    }

    /**
     * User's level relationship
     */
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * User's badges relationship
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function averageRatingReceived(): ?float
    {
        $avg = \App\Models\Review::where('reviewed_user_id', $this->id)->avg('rating');
        return $avg ? round($avg, 1) : null;
    }

    public function reviewsReceivedCount(): int
    {
        return \App\Models\Review::where('reviewed_user_id', $this->id)->count();
    }

    // Fiverr-style progression, adapted: New -> Rising Talent -> Level 2 -> Top Rated.
    // Ratings gate the higher tiers so volume alone can't buy the top badge.
    public function workerLevel(): array
    {
        $completed = $this->completedGigsCount();
        $rating = $this->averageRatingReceived();

        if ($completed >= 20 && $rating !== null && $rating >= 4.5) {
            return ['key' => 'top_rated', 'label' => 'Top Rated', 'icon' => 'trophy'];
        }
        if ($completed >= 5 && ($rating === null || $rating >= 4.0)) {
            return ['key' => 'level_2', 'label' => 'Level 2', 'icon' => 'star'];
        }
        if ($completed >= 1) {
            return ['key' => 'rising', 'label' => 'Rising Talent', 'icon' => 'trending-up'];
        }
        return ['key' => 'new', 'label' => 'New', 'icon' => 'sprout'];
    }

    // Transaction PIN gates real-money purchases (VTU, cable, withdrawals).
    // Hashed via the cast below, same treatment as password - never compared
    // or stored in plaintext.
    // Users without a verified virtual account (BVN) are capped on how much
    // they can hold in-wallet - defends against using the wallet to stash/move
    // money with zero identity verification. Earnings owed to the user (gigs,
    // marketplace sales, referral bonuses) are NOT subject to this cap - only
    // top-ups and incoming transfers, which are funds the user chooses to add.
    const NO_BVN_WALLET_CAP = 20000;

    public function wouldExceedNoBvnCap(float $incomingAmount): bool
    {
        if ($this->virtual_account_number) {
            return false;
        }
        return ($this->wallet_balance + $incomingAmount) > self::NO_BVN_WALLET_CAP;
    }

    public function hasTransactionPin(): bool
    {
        return !empty($this->transaction_pin);
    }

    public function verifyTransactionPin(string $pin): bool
    {
        return Hash::check($pin, $this->transaction_pin ?? '');
    }

    public function isAdmin(): bool
    {
        return $this->is_admin || $this->role === 'admin';
    }

    public function isCustomerCare(): bool
    {
        return $this->role === 'customer_care';
    }

    public function isCustomerCareOrAdmin(): bool
    {
        return $this->isAdmin() || $this->isCustomerCare();
    }
}
