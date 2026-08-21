<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedNumber extends Model
{
    protected $fillable = ['user_id', 'type', 'provider', 'number', 'label'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
