<?php

namespace App\Http\Controllers\TradeVista;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TradeVista\DisputeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class DisputeController extends Controller
{
    public function store(Request $request, Order $order, DisputeService $service)
    {
        if ((int) $order->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => 'required|string|max:255',
            'details' => 'required|string|max:2000',
            'evidence' => 'nullable|string',
        ]);

        $evidence = collect(explode(',', (string) $data['evidence']))
            ->filter()
            ->values()
            ->all();

        try {
            $service->open($order, Auth::user(), [
                'reason' => $data['reason'],
                'description' => $data['details'],
                'evidence' => $evidence,
            ]);
        } catch (RuntimeException $exception) {
            flash($exception->getMessage())->warning();
            return back()->withInput();
        }

        flash(translate('Your dispute has been submitted. Our team will review it shortly.'))->success();

        return back();
    }
}
