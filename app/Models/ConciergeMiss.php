<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConciergeMiss extends Model
{
    protected $fillable = ['user_id', 'message', 'occurrences', 'resolved'];
}
