<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GigMessage extends Model
{
    protected $fillable = ['gig_id', 'sender_id', 'recipient_id', 'body', 'read_at'];

    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
