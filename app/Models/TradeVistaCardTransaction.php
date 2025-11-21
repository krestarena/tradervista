<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeVistaCardTransaction extends Model
{
    protected $fillable = [
        'trade_vista_card_id',
        'merchant_id',
        'amount',
        'type',
        'reference',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(TradeVistaCard::class, 'trade_vista_card_id');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }
}
