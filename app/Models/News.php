<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = ['title', 'category', 'body', 'source_url', 'created_by', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
