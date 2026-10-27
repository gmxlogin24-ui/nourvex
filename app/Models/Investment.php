<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'investment_project_id', 'amount_q', 'status', 'idempotency_key'])]
class Investment extends Model
{
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'investment_project_id' => 'integer',
            'amount_q' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(InvestmentProject::class, 'investment_project_id');
    }
}
