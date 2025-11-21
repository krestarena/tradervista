<?php

namespace App\Services\TradeVista;

use App\Models\Order;
use App\Support\TradeVistaSettings;
use Carbon\Carbon;

class ClickAndCollectService
{
    public function applyStatus(Order $order, string $status, ?string $readyAtInput = null, $windowHoursInput = null): void
    {
        if (!$this->eligible($order)) {
            return;
        }

        $normalizedStatus = strtolower($status);

        if ($normalizedStatus === Order::DELIVERY_READY_FOR_PICKUP) {
            $this->markReady($order, $readyAtInput, $windowHoursInput);
            return;
        }

        if ($normalizedStatus === Order::DELIVERY_PICKED_UP) {
            $this->markPickedUp($order);
        }
    }

    protected function eligible(Order $order): bool
    {
        if (!TradeVistaSettings::bool('click_and_collect.enabled', true)) {
            return false;
        }

        return $order->isPickupOrder();
    }

    protected function markReady(Order $order, ?string $readyAtInput, $windowHoursInput = null): void
    {
        $readyAt = $readyAtInput ? Carbon::parse($readyAtInput) : Carbon::now();
        $windowHours = $this->resolveWindowHours($windowHoursInput);
        $windowEnd = (clone $readyAt)->addHours($windowHours);

        $order->pickup_ready_at = $readyAt;
        $order->pickup_window_end = $windowEnd;

        if ($order->dispatch_mode !== Order::DISPATCH_OWN) {
            $order->payment_protection_status = Order::PAYMENT_PROTECTION_ACTIVE;
            $order->payment_protection_hold_expires_at = $windowEnd;
        }
    }

    protected function markPickedUp(Order $order): void
    {
        $now = Carbon::now();
        $order->pickup_ready_at = $order->pickup_ready_at ?? $now;
        $order->pickup_window_end = $order->pickup_window_end ?? $now;

        if ($order->dispatch_mode !== Order::DISPATCH_OWN) {
            $order->payment_protection_status = Order::PAYMENT_PROTECTION_RELEASED;
            $order->payment_protection_released_at = $now;
        }
    }

    protected function resolveWindowHours($input): int
    {
        $hours = (int) $input;

        if ($hours <= 0) {
            $hours = TradeVistaSettings::int('click_and_collect.default_window_hours', 48);
        }

        return max($hours, 1);
    }
}
