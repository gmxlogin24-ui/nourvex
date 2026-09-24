<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id', 'tx_ref', 'amount', 'fee',
        'bank_code', 'bank_name', 'account_number', 'account_name', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
