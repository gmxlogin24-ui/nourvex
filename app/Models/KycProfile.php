<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycProfile extends Model
{
    protected $fillable = [
        'user_id', 'phone', 'id_type', 'id_front_path', 'id_back_path', 'selfie_path',
        'address_line', 'city', 'state', 'country', 'status', 'submitted_at', 'reviewed_at', 'review_note',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function completedSteps(): array
    {
        $done = [];
        if ($this->user && $this->user->virtual_account_number) {
            $done[] = 'wallet';
        }
        if ($this->id_front_path) {
            $done[] = 'id';
        }
        if ($this->selfie_path) {
            $done[] = 'selfie';
        }
        if ($this->address_line && $this->city) {
            $done[] = 'address';
        }

        return $done;
    }
}
