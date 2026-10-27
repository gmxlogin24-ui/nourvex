<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCommentLike extends Model
{
    protected $fillable = ['comment_id', 'user_id'];
}
