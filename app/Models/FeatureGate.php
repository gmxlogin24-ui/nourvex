<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['feature_key', 'label', 'required_tier', 'enabled'])]
class FeatureGate extends Model
{
    protected $casts = ['enabled' => 'boolean'];
}
