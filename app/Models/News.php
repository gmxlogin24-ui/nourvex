<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'title', 'category', 'body', 'type', 'source_url',
        'image_url', 'video_url', 'created_by',
        'published_at', 'expires_at', 'is_featured',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
        'is_featured'  => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function likes()
    {
        return $this->hasMany(NewsLike::class);
    }

    public function comments()
    {
        return $this->hasMany(NewsComment::class);
    }

    public function scopeLive($q)
    {
        return $q->where(function ($w) {
            $w->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })->where(function ($w) {
            $w->whereNull('published_at')->orWhere('published_at', '<=', now());
        });
    }
}
