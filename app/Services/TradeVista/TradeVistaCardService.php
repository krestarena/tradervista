<?php

namespace App\Services\TradeVista;

use App\Models\TradeVistaCard;
use App\Models\TradeVistaCardTransaction;
use App\Models\User;
use App\Utility\NotificationUtility;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TradeVistaCardService
{
    public function lookupByCode(string $code): ?TradeVistaCard
    {
        return TradeVistaCard::where('code', $code)->first();
    }

    public function redeem(TradeVistaCard $card, User $merchant, float $amount, ?string $reference = null): TradeVistaCardTransaction
    {
        if ($card->status !== 'active') {
            throw ValidationException::withMessages(['code' => translate('Card is not active.')]);
        }

        if ($card->isExpired()) {
            throw ValidationException::withMessages(['code' => translate('Card has expired.')]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => translate('Redemption amount must be positive.')]);
        }

        return DB::transaction(function () use ($card, $merchant, $amount, $reference) {
            $card->refresh();
            if ($card->balance < $amount) {
                throw ValidationException::withMessages(['amount' => translate('Insufficient card balance.')]);
            }

            $card->balance -= $amount;
            $card->last_redeemed_at = now();
            $card->save();

            $transaction = TradeVistaCardTransaction::create([
                'trade_vista_card_id' => $card->id,
                'merchant_id' => $merchant->id,
                'amount' => $amount,
                'type' => 'debit',
                'reference' => $reference,
                'meta' => [
                    'balance_after' => $card->balance,
                ],
            ]);

            NotificationUtility::sendTradeVistaCardRedemption($merchant, $transaction, $card);

            return $transaction;
        });
    }
}
