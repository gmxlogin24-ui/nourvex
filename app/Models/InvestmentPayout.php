<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['investment_id', 'user_id', 'investment_project_id', 'kind', 'amount_q', 'reference'])]
class InvestmentPayout extends Model
{
    protected function casts(): array
    {
        return [
            'investment_id' => 'integer',
            'user_id' => 'integer',
            'investment_project_id' => 'integer',
            'amount_q' => 'integer',
        ];
    }
}
