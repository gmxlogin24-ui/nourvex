<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentDiligence extends Model
{
    protected $table = 'investment_diligence';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['checks' => 'array'];
    }
}
