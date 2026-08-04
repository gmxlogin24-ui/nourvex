<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'cover_image'];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
