<?php

namespace App\Services\TradeVista;

use App\Models\CombinedOrder;
use App\Models\VoucherWallet;
use App\Models\VoucherWalletLedger;
use App\Models\User;
use App\Support\TradeVistaSettings;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class VoucherWalletService
{
    public const SESSION_KEY = 'tradevista_voucher_reservation';

    public function getCheckoutContext(?User $user, float $cartTotal): array
    {
        if (!$user || !TradeVistaSettings::bool('voucher_wallet_enabled')) {
            return [
                'enabled' => false,
                'balance' => 0,
                'locked_balance' => 0,
                'applied_amount' => 0,
                'suggested_amount' => 0,
                'remaining_due' => $cartTotal,
            ];
        }

        $wallet = $this->getWallet($user);
        $reservation = $this->getReservation();
        $applied = ($reservation && (int) $reservation['user_id'] === (int) $user->id) ? (float) $reservation['amount'] : 0;

        return [
            'enabled' => true,
            'balance' => $wallet->balance,
            'locked_balance' => $wallet->locked_balance,
            'applied_amount' => $applied,
            'suggested_amount' => min($wallet->balance, max($cartTotal - $applied, 0)),
            'remaining_due' => max($cartTotal - $applied, 0),
        ];
    }

    public function reserveForCheckout(User $user, float $amount, float $cartTotal): array
    {
        if (!TradeVistaSettings::bool('voucher_wallet_enabled')) {
            throw new RuntimeException(translate('Voucher wallet is disabled.'));
        }

        if ($cartTotal <= 0) {
            throw new RuntimeException(translate('Your cart is empty.'));
        }

        $normalizedAmount = round($amount, 2);
        if ($normalizedAmount <= 0) {
            throw new RuntimeException(translate('Enter a voucher amount greater than zero.'));
        }

        $normalizedAmount = min($normalizedAmount, $cartTotal);

        $wallet = $this->getWallet($user);
        if ($wallet->balance < $normalizedAmount) {
            throw new RuntimeException(translate('Insufficient voucher balance.'));
        }

        Session::put(self::SESSION_KEY, [
            'user_id' => $user->id,
            'amount' => $normalizedAmount,
            'reserved_at' => Carbon::now()->toIso8601String(),
        ]);

        return $this->getCheckoutContext($user, $cartTotal);
    }

    public function clearReservation(?User $user = null): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function finalizeForCombinedOrder(CombinedOrder $combinedOrder): ?VoucherWalletLedger
    {
        $voucherAmount = (float) $combinedOrder->voucher_deduction;
        if ($voucherAmount <= 0 || !$combinedOrder->user) {
            Session::forget(self::SESSION_KEY);
            return null;
        }

        return DB::transaction(function () use ($combinedOrder, $voucherAmount) {
            $user = $combinedOrder->user;
            $wallet = $this->getWallet($user, true);
            if ($wallet->balance < $voucherAmount) {
                throw new RuntimeException(translate('Voucher balance changed during checkout. Please try again.'));
            }

            $wallet->balance -= $voucherAmount;
            $wallet->save();

            $ledger = VoucherWalletLedger::create([
                'voucher_wallet_id' => $wallet->id,
                'type' => VoucherWalletLedger::TYPE_DEBIT,
                'amount' => $voucherAmount,
                'balance_after' => $wallet->balance,
                'reference' => $this->buildReferenceForCombinedOrder($combinedOrder),
                'meta' => [
                    'combined_order_id' => $combinedOrder->id,
                    'orders' => $combinedOrder->orders->pluck('code'),
                ],
            ]);

            Session::forget(self::SESSION_KEY);

            return $ledger;
        });
    }

    public function getReservation(): ?array
    {
        $reservation = Session::get(self::SESSION_KEY);
        return $reservation ? Arr::only($reservation, ['user_id', 'amount', 'reserved_at']) : null;
    }

    public function applyReservationToCombinedOrder(CombinedOrder $combinedOrder): float
    {
        $reservation = $this->getReservation();
        if (!$reservation || (int) $reservation['user_id'] !== (int) $combinedOrder->user_id) {
            return 0;
        }

        $amount = min((float) $reservation['amount'], (float) $combinedOrder->grand_total);
        $combinedOrder->voucher_deduction = $amount;
        $combinedOrder->grand_total = max($combinedOrder->grand_total - $amount, 0);
        Session::put(self::SESSION_KEY, array_merge($reservation, ['amount' => $amount]));

        return $amount;
    }

    public function credit(User $user, float $amount, string $reference, array $meta = []): VoucherWalletLedger
    {
        if ($amount <= 0) {
            throw new RuntimeException(translate('Voucher amount must be greater than zero.'));
        }

        return DB::transaction(function () use ($user, $amount, $reference, $meta) {
            $wallet = $this->getWallet($user, true);
            $wallet->balance += $amount;
            $wallet->save();

            return VoucherWalletLedger::create([
                'voucher_wallet_id' => $wallet->id,
                'type' => VoucherWalletLedger::TYPE_CREDIT,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'reference' => $reference,
                'meta' => $meta,
            ]);
        });
    }

    protected function getWallet(User $user, bool $lockForUpdate = false): VoucherWallet
    {
        $query = VoucherWallet::query();
        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'balance' => 0,
            'locked_balance' => 0,
        ]);
    }

    protected function buildReferenceForCombinedOrder(CombinedOrder $combinedOrder): string
    {
        $orderCode = optional($combinedOrder->orders->first())->code;
        return $orderCode ? 'order:' . $orderCode : 'combined:' . $combinedOrder->id;
    }
}
