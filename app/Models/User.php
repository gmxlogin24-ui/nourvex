<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Illuminate\Support\Facades\Hash;
use App\Events\TierUpgraded;

#[Fillable([
    'name', 'email', 'password', 'gender', 'spoken_language', 'avatar_url', 'background_url', 'accent_color',
    'referral_code', 'referred_by', 'wallet_balance', 'q_coin_balance', 'q_coin_last_claim_date',
    'tier', 'tier_upgraded_at', 'is_verified_seller',
    'virtual_account_number', 'virtual_account_bank', 'virtual_account_ref',
    'account_type', 'business_name', 'business_logo', 'business_whatsapp', 'is_vendor_active', 'interests',
    'streak_days', 'last_active_date', 'leaderboard_opt_in', 'q_coin_streak_days', 'wallet_restricted', 'google_id', 'transaction_pin', 'theme_style', 'mobile_nav_style', 'sidebar_style',
    'is_admin', 'role', 'level_id', 'has_seen_tour',
    'signup_fee_paid', 'badge_tier', 'card_token', 'card_last4', 'card_type',
    'is_banned', 'subscription_expires_at', 'skills',
    'notif_style', 'navbar_style', 'topbar_style', 'card_style', 'area', 'area_verified_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasPushSubscriptions, HasPlanRules;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'transaction_pin' => 'hashed',
            'interests' => 'array',
            'customer_care_permissions' => 'array',
            'is_admin' => 'boolean',
            'leaderboard_opt_in' => 'boolean',
            'wallet_restricted' => 'boolean',
            'is_vendor_active' => 'boolean',
            'tier_upgraded_at' => 'datetime', 'area_verified_at' => 'datetime',
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
        $cap = $this->walletCap();
        if ($cap === null) {
            return false;
        }
        return ($this->wallet_balance + $incomingAmount) > $cap;
    }
    public function kycLevel(): int
    {
        // 0 = no BVN linked. 1 = BVN linked. 2 = ID approved by an admin.
        // 3 = BVN linked, ID approved, and address and selfie on file and approved.
        $level = $this->virtual_account_number ? 1 : 0;
        $kyc = \Illuminate\Support\Facades\DB::table('kyc_profiles')
            ->where('user_id', $this->id)->where('status', 'approved')->first();
        if ($kyc && !empty($kyc->id_type) && !empty($kyc->id_front_path)) {
            $level = max($level, 2);
            if ($this->virtual_account_number && !empty($kyc->address_line) && !empty($kyc->selfie_path)) {
                $level = 3;
            }
        }
        return $level;
    }

    public function walletCap(): ?float
    {
        // null means no cap at this level.
        $cap = config('kyc.wallet_cap.' . $this->kycLevel());
        return $cap === null ? null : (float) $cap;
    }
    public function hasTransactionPin(): bool
    {
        return !empty($this->transaction_pin);
    }

    public function verifyTransactionPin(string $pin): bool
    {
        // After 5 wrong tries the PIN is locked for 15 minutes. While locked, even the right PIN is refused,
        // so nobody can keep guessing.
        $key = 'pin-fail:' . $this->id;
        if ((int) \Illuminate\Support\Facades\Cache::get($key, 0) >= 5) {
            return false;
        }
        $ok = Hash::check($pin, $this->transaction_pin ?? '');
        if ($ok) {
            \Illuminate\Support\Facades\Cache::forget($key);
        } else {
            \Illuminate\Support\Facades\Cache::add($key, 0, now()->addMinutes(15));
            \Illuminate\Support\Facades\Cache::increment($key);
        }
        return $ok;
    }

    public function pinLocked(): bool
    {
        return (int) \Illuminate\Support\Facades\Cache::get('pin-fail:' . $this->id, 0) >= 5;
    }

    public function pinErrorMessage(): string
    {
        return $this->pinLocked() ? 'Too many wrong PINs. Try again in 15 minutes.' : 'Incorrect transaction PIN.';
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

    public function hasCcPermission(string $key): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return in_array($key, $this->customer_care_permissions ?? [], true);
    }
}
