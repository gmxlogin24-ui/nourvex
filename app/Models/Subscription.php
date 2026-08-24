<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    const PLANS = [
        'tier1' => ['label' => 'Basic',   'price' => 5000],
        'tier2' => ['label' => 'Pro',     'price' => 8000],
        'tier3' => ['label' => 'Premium', 'price' => 15000],
    ];

    protected $fillable = [
        'user_id', 'plan', 'amount', 'payment_method',
        'flutterwave_tx_ref', 'status', 'starts_at', 'expires_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('expires_at', '>', now());
    }

    public static function currentFor(User $user)
    {
        return static::where('user_id', $user->id)->active()->latest('expires_at')->first();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expires_at->diffInDays(now()) <= 14 && $this->expires_at->isFuture();
    }

    /**
     * Activate a pending subscription: mark it active, update the user's tier,
     * log the transaction, and pay any referral bonus. Safe to call from both
     * the wallet-payment path and the Flutterwave callback — only acts if
     * still pending, and is the single source of truth for what "activating"
     * a subscription means so the two payment paths can't drift apart.
     */
    public function activate(): void
    {
        if ($this->status === 'active') {
            return; // already processed, idempotent
        }

        $user = $this->user;
        if (!$user) {
            return;
        }

        $startsAt = now();
        $expiresAt = now()->addYear();

        $this->update(['status' => 'active', 'starts_at' => $startsAt, 'expires_at' => $expiresAt]);

        $updateData = [
            'tier' => $this->plan,
            'tier_upgraded_at' => $startsAt,
            'subscription_expires_at' => $expiresAt,
        ];
        if (in_array($this->plan, ['tier2', 'tier3'], true)) {
            $updateData['is_verified_seller'] = true;
        }
        $user->update($updateData);

        \App\Models\Transaction::create([
            'user_id' => $user->id,
            'type' => 'tier_upgrade',
            'amount' => -$this->amount,
            'description' => 'Subscribed to ' . self::PLANS[$this->plan]['label'] . ' (1 year)',
        ]);

        if ($user->referred_by) {
            $referrer = \App\Models\User::find($user->referred_by);
            if ($referrer && !$referrer->wallet_restricted) {
                $pct = in_array($referrer->tier, ['tier2', 'tier3'], true) ? 0.15 : 0;
                $bonus = $this->amount * $pct;
                if ($bonus > 0) {
                    $referrer->increment('wallet_balance', $bonus);
                    \App\Models\Transaction::create([
                        'user_id' => $referrer->id,
                        'type' => 'referral_bonus',
                        'amount' => $bonus,
                        'description' => 'Referral bonus from ' . $user->name . ' subscribing to ' . strtoupper($this->plan),
                    ]);
                }
            }
        }
    }
}
