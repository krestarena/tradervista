<?php

namespace App\Services\TradeVista;

use App\Models\CommissionHistory;
use App\Models\Order;
use App\Models\ReferralRewardJob;
use App\Models\User;
use App\Support\TradeVistaSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReferralRewardService
{
    protected VoucherWalletService $walletService;

    public function __construct(VoucherWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function queueForOrder(Order $order): void
    {
        if (!$this->orderQualifies($order)) {
            return;
        }

        $order->loadMissing('user', 'orderDetails');
        if (!$order->user) {
            return;
        }

        $commissionRows = CommissionHistory::where('order_id', $order->id)->get();
        if ($commissionRows->isEmpty()) {
            return;
        }

        $releaseAt = $this->determineReleaseAt($order);

        $this->queueBuyerReward($order, $commissionRows->sum('admin_commission'), $releaseAt);
        $this->queueSellerRewards($order, $commissionRows, $releaseAt);
    }

    public function releaseForOrder(Order $order): void
    {
        ReferralRewardJob::where('payload->order_id', $order->id)
            ->where('status', ReferralRewardJob::STATUS_PENDING)
            ->update(['run_at' => Carbon::now()]);
    }

    public function processPendingJobs(int $limit = 100): int
    {
        $jobs = ReferralRewardJob::where('status', ReferralRewardJob::STATUS_PENDING)
            ->where(function ($query) {
                $query->whereNull('run_at')->orWhere('run_at', '<=', Carbon::now());
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($jobs as $job) {
            DB::transaction(function () use ($job) {
                $payload = $job->payload ?? [];
                $userId = $payload['referrer_id'] ?? null;
                $amount = (float) ($payload['amount'] ?? 0);

                if (!$userId || $amount <= 0) {
                    $job->status = ReferralRewardJob::STATUS_FAILED;
                    $job->payload = array_merge($payload, ['error' => 'invalid_payload']);
                    $job->processed_at = Carbon::now();
                    $job->save();
                    return;
                }

                $user = User::find($userId);
                if (!$user) {
                    $job->status = ReferralRewardJob::STATUS_FAILED;
                    $job->payload = array_merge($payload, ['error' => 'user_missing']);
                    $job->processed_at = Carbon::now();
                    $job->save();
                    return;
                }

                $reference = $job->reference ?? ('referral:' . ($payload['type'] ?? 'unknown') . ':' . $job->id);
                $meta = [
                    'order_id' => $payload['order_id'] ?? null,
                    'order_code' => $payload['order_code'] ?? null,
                    'type' => $payload['type'] ?? null,
                    'seller_id' => $payload['seller_id'] ?? null,
                ];

                $this->walletService->credit($user, $amount, $reference, $meta);

                $job->status = ReferralRewardJob::STATUS_PROCESSED;
                $job->processed_at = Carbon::now();
                $job->save();
            });
        }

        return $jobs->count();
    }

    protected function queueBuyerReward(Order $order, float $commissionTotal, Carbon $releaseAt): void
    {
        $buyer = $order->user;
        if (!$buyer || !$buyer->referred_by) {
            return;
        }

        $buyerPct = TradeVistaSettings::float('referral.buyer_pct', 10);
        if ($buyerPct <= 0) {
            return;
        }

        $amount = round($commissionTotal * ($buyerPct / 100), 2);
        if ($amount <= 0) {
            return;
        }

        $this->upsertJob(
            reference: $this->buildReference('buyer', $order->id),
            payload: [
                'type' => 'buyer',
                'order_id' => $order->id,
                'order_code' => $order->code,
                'referrer_id' => $buyer->referred_by,
                'amount' => $amount,
            ],
            releaseAt: $releaseAt
        );
    }

    protected function queueSellerRewards(Order $order, Collection $commissionRows, Carbon $releaseAt): void
    {
        if ($commissionRows->isEmpty()) {
            return;
        }

        $sellerPct = TradeVistaSettings::float('referral.seller_pct', 5);
        if ($sellerPct <= 0) {
            return;
        }

        $grouped = $commissionRows->groupBy('seller_id');
        foreach ($grouped as $sellerId => $rows) {
            if (!$sellerId) {
                continue;
            }

            $seller = User::find($sellerId);
            if (!$seller || !$seller->referred_by) {
                continue;
            }

            $commissionTotal = (float) $rows->sum('admin_commission');
            if ($commissionTotal <= 0) {
                continue;
            }

            $amount = round($commissionTotal * ($sellerPct / 100), 2);
            if ($amount <= 0) {
                continue;
            }

            $this->upsertJob(
                reference: $this->buildReference('seller', $order->id, $sellerId),
                payload: [
                    'type' => 'seller',
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'seller_id' => $sellerId,
                    'referrer_id' => $seller->referred_by,
                    'amount' => $amount,
                ],
                releaseAt: $releaseAt
            );
        }
    }

    protected function upsertJob(string $reference, array $payload, Carbon $releaseAt): void
    {
        $job = ReferralRewardJob::firstOrNew(['reference' => $reference]);

        if ($job->exists && $job->status !== ReferralRewardJob::STATUS_PENDING) {
            return;
        }

        $job->status = ReferralRewardJob::STATUS_PENDING;
        $job->payload = $payload;
        $job->run_at = $releaseAt;
        $job->reference = $reference;
        $job->save();
    }

    protected function determineReleaseAt(Order $order): Carbon
    {
        if ($order->dispatch_mode === Order::DISPATCH_OWN) {
            return Carbon::now();
        }

        if ($order->payment_protection_status === Order::PAYMENT_PROTECTION_RELEASED && $order->payment_protection_released_at) {
            return Carbon::parse($order->payment_protection_released_at);
        }

        if ($order->payment_protection_hold_expires_at) {
            return Carbon::parse($order->payment_protection_hold_expires_at);
        }

        $windowDays = TradeVistaSettings::int('payment_protection_window_days', 5);
        return Carbon::now()->addDays(max($windowDays, 0));
    }

    protected function buildReference(string $type, int $orderId, ?int $sellerId = null): string
    {
        return $sellerId ? sprintf('%s:%d:%d', $type, $orderId, $sellerId) : sprintf('%s:%d', $type, $orderId);
    }

    protected function orderQualifies(Order $order): bool
    {
        if ($order->delivery_status !== 'delivered') {
            return false;
        }

        $minOrder = TradeVistaSettings::float('referral.min_order', 10000);
        return (float) $order->grand_total >= $minOrder;
    }
}
