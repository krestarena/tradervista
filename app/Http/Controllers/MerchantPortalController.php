<?php

namespace App\Http\Controllers;

use App\Models\TradeVistaCard;
use App\Models\TradeVistaCardTransaction;
use App\Services\TradeVista\TradeVistaCardService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MerchantPortalController extends Controller
{
    public function __construct(private TradeVistaCardService $cardService)
    {
    }

    public function index()
    {
        abort_unless(config('tradevista.voucher_redemption_portal_enabled'), 404);

        $transactions = TradeVistaCardTransaction::with('card')
            ->where('merchant_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();

        return view('merchant.portal.index', compact('transactions'));
    }

    public function lookup(Request $request)
    {
        abort_unless(config('tradevista.voucher_redemption_portal_enabled'), 404);

        $request->validate([
            'code' => 'required|string',
        ]);

        $card = $this->cardService->lookupByCode($request->code);

        if (!$card) {
            return response()->json(['message' => translate('Card not found')], 404);
        }

        return [
            'code' => $card->code,
            'balance' => $card->balance,
            'status' => $card->status,
            'expires_at' => optional($card->expires_at)->toDateTimeString(),
        ];
    }

    public function redeem(Request $request)
    {
        abort_unless(config('tradevista.voucher_redemption_portal_enabled'), 404);

        $data = $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:191',
        ]);

        $card = $this->cardService->lookupByCode($data['code']);
        if (!$card) {
            return response()->json(['message' => translate('Card not found')], 404);
        }

        $transaction = $this->cardService->redeem($card, $request->user(), (float) $data['amount'], $data['reference'] ?? null);

        return response()->json([
            'message' => translate('Redemption successful'),
            'balance' => $card->balance,
            'transaction_id' => $transaction->id,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless(config('tradevista.voucher_redemption_portal_enabled'), 404);

        $transactions = TradeVistaCardTransaction::with('card')
            ->where('merchant_id', auth()->id())
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tradevista-card-transactions.csv"',
        ];

        $callback = function () use ($transactions) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Card Code', 'Amount', 'Type', 'Reference', 'Balance After', 'Created At']);
            foreach ($transactions as $txn) {
                fputcsv($output, [
                    $txn->id,
                    $txn->card->code,
                    $txn->amount,
                    $txn->type,
                    $txn->reference,
                    $txn->meta['balance_after'] ?? '',
                    $txn->created_at,
                ]);
            }
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
