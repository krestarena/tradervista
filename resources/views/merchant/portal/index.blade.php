@extends('frontend.layouts.app')

@section('content')
<section class="pt-4 pb-4">
    <div class="container">
        <div class="row gutters-10">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 h6">{{ translate('TradeVista Merchant Portal') }}</h5>
                        <div>
                            <a href="{{ route('merchant.cards.export') }}" class="btn btn-soft-primary btn-sm">{{ translate('Export CSV') }}</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="redeem-form" class="row gutters-10 align-items-end mb-4">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label">{{ translate('Card Code / QR token') }}</label>
                                <input type="text" name="code" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ translate('Amount to Redeem') }}</label>
                                <input type="number" name="amount" class="form-control" min="0.01" step="0.01" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ translate('Reference (optional)') }}</label>
                                <input type="text" name="reference" class="form-control">
                            </div>
                            <div class="col-md-2 text-right">
                                <button type="submit" class="btn btn-primary btn-block">{{ translate('Redeem') }}</button>
                            </div>
                        </form>

                        <div class="alert alert-soft-secondary" id="card-status" style="display:none;"></div>

                        <h6 class="mt-4">{{ translate('Recent redemptions') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ translate('Card') }}</th>
                                        <th>{{ translate('Amount') }}</th>
                                        <th>{{ translate('Reference') }}</th>
                                        <th>{{ translate('Balance After') }}</th>
                                        <th>{{ translate('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $txn)
                                        <tr>
                                            <td>{{ $txn->card->code }}</td>
                                            <td>{{ single_price($txn->amount) }}</td>
                                            <td>{{ $txn->reference }}</td>
                                            <td>{{ $txn->meta['balance_after'] ?? '' }}</td>
                                            <td>{{ $txn->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">{{ translate('No redemptions yet.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script>
    (function(){
        const form = document.getElementById('redeem-form');
        const statusBox = document.getElementById('card-status');
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            statusBox.style.display = 'none';
            const formData = new FormData(form);
            fetch("{{ route('merchant.cards.redeem') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': formData.get('_token') },
                body: formData
            }).then(async response => {
                const payload = await response.json();
                statusBox.style.display = 'block';
                statusBox.className = response.ok ? 'alert alert-success' : 'alert alert-danger';
                statusBox.innerText = payload.message || '{{ translate('Something went wrong') }}';
            }).catch(() => {
                statusBox.style.display = 'block';
                statusBox.className = 'alert alert-danger';
                statusBox.innerText = '{{ translate('Unable to redeem at this time.') }}';
            });
        });
    })();
</script>
@endsection
