<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['code', 'role', 'created_by', 'expires_at'])]
class Invite extends Model
{
    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public static function generate(int $createdByUserId, string $role = 'customer_care', int $expiresInDays = 7): self
    {
        return self::create([
            'code' => strtoupper(Str::random(10)),
            'role' => $role,
            'created_by' => $createdByUserId,
            'expires_at' => now()->addDays($expiresInDays),
        ]);
    }

    public function isValid(): bool
    {
        return is_null($this->used_at) && $this->expires_at->isFuture();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
