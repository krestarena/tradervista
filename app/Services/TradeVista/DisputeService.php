<?php

namespace App\Services\TradeVista;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use App\Utility\NotificationUtility;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DisputeService
{
    public function __construct(protected ReferralRewardService $referralRewardService)
    {
    }

    public function canOpen(Order $order): bool
    {
        if (!$this->hasDelivered($order)) {
            return false;
        }

        if ($order->payment_protection_status === Order::PAYMENT_PROTECTION_RELEASED) {
            return false;
        }

        $deadline = $this->deadline($order);
        if ($deadline && Carbon::now()->greaterThan($deadline)) {
            return false;
        }

        return !$order->disputes()->where('status', '!=', Dispute::STATUS_RESOLVED)->exists();
    }

    public function eligibility(Order $order): array
    {
        return [
            'can_open' => $this->canOpen($order),
            'deadline' => $this->deadline($order),
            'window_hours' => (int) config('tradevista.dispute_window_hours', 48),
        ];
    }

    public function open(Order $order, User $user, array $payload): Dispute
    {
        if ((int) $order->user_id !== (int) $user->id) {
            throw new RuntimeException(translate('You are not allowed to dispute this order.'));
        }

        if (!$this->canOpen($order)) {
            throw new RuntimeException(translate('Disputes can no longer be filed for this order.'));
        }

        return DB::transaction(function () use ($order, $user, $payload) {
            $dispute = $order->disputes()->create([
                'submitted_by' => $user->id,
                'reason' => $payload['reason'] ?? null,
                'description' => $payload['description'] ?? null,
                'evidence' => $payload['evidence'] ?? [],
                'status' => Dispute::STATUS_OPEN,
            ]);

            $this->pausePaymentProtection($order);
            NotificationUtility::sendNotification($order, 'dispute_opened');

            return $dispute;
        });
    }

    public function pausePaymentProtection(Order $order): void
    {
        if ($order->payment_protection_status === Order::PAYMENT_PROTECTION_RELEASED) {
            return;
        }

        $order->payment_protection_status = Order::PAYMENT_PROTECTION_PAUSED;
        $order->saveQuietly();
    }

    public function startReview(Dispute $dispute): Dispute
    {
        if ($dispute->status === Dispute::STATUS_OPEN) {
            $dispute->status = Dispute::STATUS_UNDER_REVIEW;
            $dispute->save();
        }

        return $dispute;
    }

    public function resolve(Dispute $dispute, User $admin, string $resolution, string $notes, ?float $amount = null): Dispute
    {
        if ($dispute->status === Dispute::STATUS_RESOLVED) {
            return $dispute;
        }

        return DB::transaction(function () use ($dispute, $admin, $resolution, $notes, $amount) {
            $dispute->status = Dispute::STATUS_RESOLVED;
            $dispute->resolution = $resolution;
            $dispute->decision_notes = $notes;
            $dispute->decision_amount = $amount;
            $dispute->resolved_by = $admin->id;
            $dispute->resolved_at = Carbon::now();
            $dispute->save();

            $order = $dispute->order;
            if ($order) {
                $order->payment_protection_status = Order::PAYMENT_PROTECTION_RELEASED;
                $order->payment_protection_released_at = Carbon::now();
                $order->saveQuietly();
                $this->referralRewardService->releaseForOrder($order);
                NotificationUtility::sendNotification($order, 'dispute_resolved');
            }

            return $dispute;
        });
    }

    protected function hasDelivered(Order $order): bool
    {
        return $order->delivery_status === 'delivered'
            || ($order->delivery_status === Order::DELIVERY_PICKED_UP && $order->isPickupOrder());
    }

    protected function deadline(Order $order): ?Carbon
    {
        $windowHours = (int) config('tradevista.dispute_window_hours', 48);
        $deliveredAt = $order->delivered_date ? Carbon::parse($order->delivered_date) : ($this->hasDelivered($order) ? Carbon::parse($order->updated_at) : null);
        if (!$deliveredAt) {
            return null;
        }

        return $deliveredAt->copy()->addHours(max($windowHours, 0));
    }
}
