<?php

namespace App\Http\Controllers\Admin\TradeVista;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Services\TradeVista\DisputeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $disputes = Dispute::with(['order.user', 'submittedBy'])
            ->when($status, fn($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->appends($request->all());

        return view('backend.tradevista.disputes.index', compact('disputes', 'status'));
    }

    public function show(Dispute $dispute)
    {
        $dispute->load(['order.user', 'order.shop', 'submittedBy', 'resolver']);
        return view('backend.tradevista.disputes.show', compact('dispute'));
    }

    public function startReview(Dispute $dispute, DisputeService $service)
    {
        $service->startReview($dispute);
        flash(translate('Dispute marked as under review.'))->success();

        return back();
    }

    public function resolve(Request $request, Dispute $dispute, DisputeService $service)
    {
        $data = $request->validate([
            'resolution' => 'required|in:refund,release_funds,partial',
            'decision_notes' => 'required|string|max:2000',
            'decision_amount' => 'nullable|numeric|min:0',
            'confirm_resolution' => 'accepted',
        ]);

        if ($data['resolution'] === Dispute::RESOLUTION_PARTIAL && empty($data['decision_amount'])) {
            return back()->withErrors(['decision_amount' => translate('Amount is required for partial resolutions.')])->withInput();
        }

        $service->resolve(
            $dispute,
            Auth::user(),
            $data['resolution'],
            $data['decision_notes'],
            $data['decision_amount'] ?? null
        );

        flash(translate('Dispute resolved successfully.'))->success();

        return redirect()->route('admin.tradevista.disputes.show', $dispute);
    }
}
