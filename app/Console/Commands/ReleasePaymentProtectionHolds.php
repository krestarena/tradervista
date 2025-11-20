<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\TradeVista\ReferralRewardService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReleasePaymentProtectionHolds extends Command
{
    protected $signature = 'tradevista:release-payment-protection {--limit=200 : Maximum orders to release per run}';

    protected $description = 'Release Payment Protection holds that have reached their expiry window.';

    public function handle(ReferralRewardService $referralRewardService): int
    {
        $limit = (int) $this->option('limit');
        $released = 0;

        $orders = Order::where('payment_protection_status', Order::PAYMENT_PROTECTION_ACTIVE)
            ->where('delivery_status', 'delivered')
            ->whereNotNull('payment_protection_hold_expires_at')
            ->where('payment_protection_hold_expires_at', '<=', Carbon::now())
            ->orderBy('payment_protection_hold_expires_at')
            ->limit(max($limit, 1))
            ->get();

        foreach ($orders as $order) {
            $order->payment_protection_status = Order::PAYMENT_PROTECTION_RELEASED;
            $order->payment_protection_released_at = Carbon::now();
            $order->saveQuietly();
            $referralRewardService->releaseForOrder($order);
            $released++;
        }

        $this->info("Released {$released} order(s) from Payment Protection hold.");

        return self::SUCCESS;
    }
}
