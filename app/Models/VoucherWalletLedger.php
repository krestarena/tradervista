<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherWalletLedger extends Model
{
    public const TYPE_DEBIT = 'debit';
    public const TYPE_CREDIT = 'credit';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(VoucherWallet::class, 'voucher_wallet_id');
    }
}
