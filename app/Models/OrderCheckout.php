<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCheckout extends Model
{
    protected $fillable = ['user_id', 'tx_ref', 'amount', 'kind', 'target_id', 'status', 'paid_at'];

    protected $casts = [
        'paid_at' => 'datetime',
    ];
}
