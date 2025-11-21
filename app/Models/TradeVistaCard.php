<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TradeVistaCard extends Model
{
    protected $fillable = [
        'code',
        'balance',
        'locked_balance',
        'status',
        'expires_at',
        'last_redeemed_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'expires_at' => 'datetime',
        'last_redeemed_at' => 'datetime',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(TradeVistaCardTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && Carbon::now()->greaterThan($this->expires_at);
    }
}
