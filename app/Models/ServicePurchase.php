<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePurchase extends Model
{
    protected $fillable = ['user_id', 'service_type', 'network', 'phone', 'plan_code', 'amount', 'status', 'provider_reference'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
