<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\TradeVista\ReferralRewardService;
use Carbon\Carbon;

class OrderObserver
{
    protected ReferralRewardService $referralRewardService;

    public function __construct(ReferralRewardService $referralRewardService)
    {
        $this->referralRewardService = $referralRewardService;
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('delivery_status') && $order->delivery_status === 'delivered') {
            $this->ensurePaymentProtectionWindow($order);
            $this->referralRewardService->queueForOrder($order);
        }

        if ($order->wasChanged('payment_protection_status') && $order->payment_protection_status === Order::PAYMENT_PROTECTION_RELEASED) {
            $this->referralRewardService->releaseForOrder($order);
        }
    }

    protected function ensurePaymentProtectionWindow(Order $order): void
    {
        if ($order->dispatch_mode === Order::DISPATCH_OWN) {
            if (!$order->payment_protection_released_at) {
                $order->payment_protection_status = Order::PAYMENT_PROTECTION_RELEASED;
                $order->payment_protection_released_at = Carbon::now();
                $order->saveQuietly();
            }
            return;
        }

        if (!$order->payment_protection_hold_expires_at) {
            $windowDays = (int) config('tradevista.payment_protection_window_days', 5);
            $order->payment_protection_hold_expires_at = Carbon::now()->addDays(max($windowDays, 0));
            $order->payment_protection_status = Order::PAYMENT_PROTECTION_ACTIVE;
            $order->saveQuietly();
        }
    }
}
