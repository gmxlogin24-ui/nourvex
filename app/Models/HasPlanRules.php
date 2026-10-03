<?php

namespace App\Models;

trait HasPlanRules
{
    public function planKey(): string
    {
        $tier = $this->tier ?? 'free';
        return config('plans.' . $tier) ? $tier : 'free';
    }

    public function planLabel(): string
    {
        return Subscription::PLANS[$this->planKey()]['label'] ?? 'Free';
    }

    public function planRule(string $key, $default = null)
    {
        return config('plans.' . $this->planKey() . '.' . $key, $default);
    }

    // Use in Blade: @if(auth()->user()->planAllows('gigs_post'))
    public function planAllows(string $key): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        $v = $this->planRule($key, false);
        if ($v === null) {
            return true;
        }
        if (is_bool($v)) {
            return $v;
        }
        if (is_numeric($v)) {
            return $v > 0;
        }
        return (bool) $v;
    }
}
