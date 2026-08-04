<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GigView extends Model
{
    protected $fillable = ['user_id', 'gig_id', 'views_count'];
}
