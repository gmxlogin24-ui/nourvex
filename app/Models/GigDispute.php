<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GigDispute extends Model
{
    protected $fillable = [
        'gig_id', 'raised_by', 'description', 'status', 'resolved_by', 'resolved_at', 'assigned_to', 'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'assigned_at' => 'datetime',
        ];
    }

    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }

    public function raisedBy()
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
