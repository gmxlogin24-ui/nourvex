<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['flaggable_type', 'flaggable_id', 'reason', 'raised_by', 'status'])]
class Flag extends Model
{
    public function raisedBy()
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function flaggable()
    {
        return $this->morphTo();
    }
}
