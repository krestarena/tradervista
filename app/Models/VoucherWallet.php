<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherWallet extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ledgers()
    {
        return $this->hasMany(VoucherWalletLedger::class);
    }
}
