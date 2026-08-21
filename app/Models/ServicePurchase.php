<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePurchase extends Model
{
    protected $fillable = ['user_id', 'service_type', 'network', 'phone', 'plan_code', 'amount', 'cost', 'status', 'provider_reference', 'idempotency_key'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
