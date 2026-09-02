<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'amount', 'balance_after', 'type', 'description', 'reference_id'])]
class QCoinTransaction extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
