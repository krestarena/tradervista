<?php

namespace App\Http\Controllers\Admin\TradeVista;

use App\Http\Controllers\Controller;
use App\Models\CommissionHistory;
use App\Models\Dispute;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $now = now();

        $gmvLast30 = Order::where('created_at', '>=', $now->copy()->subDays(30))->sum('grand_total');
        $gmvLast7 = Order::where('created_at', '>=', $now->copy()->subDays(7))->sum('grand_total');

        $voucherLiability = DB::table('voucher_wallets')->sum('balance');
        $heldOrdersCount = Order::where('payment_protection_status', Order::PAYMENT_PROTECTION_ACTIVE)->count();
        $openDisputes = Dispute::where('status', '!=', Dispute::STATUS_RESOLVED)->count();
        $pendingReferralJobs = DB::table('referral_reward_jobs')->whereNull('processed_at')->count();

        $commissionHeld = CommissionHistory::whereHas('order', function ($query) {
            $query->where('payment_protection_status', Order::PAYMENT_PROTECTION_ACTIVE);
        })->count();

        return view('backend.tradevista.reports.index', [
            'gmvLast30' => $gmvLast30,
            'gmvLast7' => $gmvLast7,
            'voucherLiability' => $voucherLiability,
            'heldOrdersCount' => $heldOrdersCount,
            'openDisputes' => $openDisputes,
            'pendingReferralJobs' => $pendingReferralJobs,
            'commissionHeld' => $commissionHeld,
        ]);
    }

    public function exportVoucherLiability(Request $request): StreamedResponse
    {
        $filename = 'voucher-liability-' . now()->format('Ymd_His') . '.csv';

        $rows = DB::table('voucher_wallets')
            ->leftJoin('users', 'users.id', '=', 'voucher_wallets.user_id')
            ->select(
                'voucher_wallets.id',
                'users.id as user_id',
                'users.name',
                'users.email',
                'voucher_wallets.balance',
                'voucher_wallets.locked_balance',
                'voucher_wallets.expiry_date',
                'voucher_wallets.updated_at'
            )
            ->orderByDesc('voucher_wallets.updated_at')
            ->get();

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Voucher Wallet ID',
                'User ID',
                'Name',
                'Email',
                'Balance',
                'Locked Balance',
                'Expiry Date',
                'Last Updated',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->user_id,
                    $row->name,
                    $row->email,
                    $row->balance,
                    $row->locked_balance,
                    $row->expiry_date ? Carbon::parse($row->expiry_date)->format('Y-m-d') : null,
                    $row->updated_at ? Carbon::parse($row->updated_at)->format('Y-m-d H:i:s') : null,
                ]);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportCommissionHoldQueue(Request $request): StreamedResponse
    {
        $filename = 'commission-hold-queue-' . now()->format('Ymd_His') . '.csv';

        $rows = CommissionHistory::with(['order.user', 'order.shop'])
            ->whereHas('order', function ($query) {
                $query->where('payment_protection_status', Order::PAYMENT_PROTECTION_ACTIVE);
            })
            ->orderByDesc('created_at')
            ->get();

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Commission ID',
                'Order Code',
                'Seller',
                'Buyer',
                'Commission',
                'Order Total',
                'Dispatch Mode',
                'Payment Protection Status',
                'Created At',
            ]);

            foreach ($rows as $history) {
                $order = $history->order;
                fputcsv($handle, [
                    $history->id,
                    optional($order)->code,
                    optional(optional($order)->shop)->name,
                    optional(optional($order)->user)->name,
                    $history->commission,
                    optional($order)->grand_total,
                    optional($order)->dispatch_mode,
                    optional($order)->payment_protection_status,
                    optional($history->created_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
