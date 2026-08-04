<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTopup extends Model
{
    protected $fillable = ['user_id', 'tx_ref', 'amount', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
