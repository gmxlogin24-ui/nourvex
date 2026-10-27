<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'owner_id', 'type', 'title', 'description', 'goal_q', 'min_invest_q', 'max_invest_q',
    'raised_q', 'released_q', 'returns_reported_q', 'returns_distributed_q',
    'status', 'closes_at', 'approved_at',
])]
class InvestmentProject extends Model
{
    protected function casts(): array
    {
        return [
            'owner_id' => 'integer',
            'goal_q' => 'integer',
            'min_invest_q' => 'integer',
            'max_invest_q' => 'integer',
            'raised_q' => 'integer',
            'released_q' => 'integer',
            'returns_reported_q' => 'integer',
            'returns_distributed_q' => 'integer',
            'closes_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function investments()
    {
        return $this->hasMany(Investment::class, 'investment_project_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open' && $this->closes_at !== null && $this->closes_at->isFuture();
    }

    public function remainingGoal(): int
    {
        return max(0, $this->goal_q - $this->raised_q);
    }

    public function availableToRelease(): int
    {
        return max(0, $this->raised_q - $this->released_q);
    }

    public function availableReturns(): int
    {
        return max(0, $this->returns_reported_q - $this->returns_distributed_q);
    }

    public function progressPercent(): int
    {
        if ($this->goal_q <= 0) {
            return 0;
        }

        return (int) min(100, round($this->raised_q / $this->goal_q * 100));
    }
}
