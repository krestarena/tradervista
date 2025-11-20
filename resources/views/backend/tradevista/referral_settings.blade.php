@extends('backend.layouts.app')

@section('content')
    <div class="row gutters-10">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('TradeVista Referral Settings') }}</h5>
                </div>
                <form action="{{ route('admin.tradevista.referral-settings.update') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">{{ translate('Buyer referral (%)') }}</label>
                            <input type="number" class="form-control" name="buyer_pct" value="{{ old('buyer_pct', $settings['buyer_pct']) }}" min="0" max="100" step="0.1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ translate('Seller referral (%)') }}</label>
                            <input type="number" class="form-control" name="seller_pct" value="{{ old('seller_pct', $settings['seller_pct']) }}" min="0" max="100" step="0.1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ translate('Minimum qualifying order (₦)') }}</label>
                            <input type="number" class="form-control" name="min_order" value="{{ old('min_order', $settings['min_order']) }}" min="0" step="1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ translate('Voucher expiry (days)') }}</label>
                            <input type="number" class="form-control" name="voucher_expiry_days" value="{{ old('voucher_expiry_days', $settings['voucher_expiry_days']) }}" min="30" max="1095" step="1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ translate('Rounding mode') }}</label>
                            <select name="rounding_mode" class="form-control aiz-selectpicker" required>
                                @foreach (['nearest' => translate('Nearest kobo'), 'up' => translate('Always round up'), 'down' => translate('Always round down')] as $mode => $label)
                                    <option value="{{ $mode }}" @selected(old('rounding_mode', $settings['rounding_mode']) == $mode)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="confirm_changes" name="confirm_changes" value="1" required>
                                <label class="form-check-label" for="confirm_changes">{{ translate('I understand these changes apply to future transactions only.') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">{{ translate('Save Settings') }}</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">{{ translate('Recent Updates') }}</h6>
                </div>
                <div class="card-body">
                    @if ($audits->isEmpty())
                        <p class="text-muted mb-0">{{ translate('No updates have been recorded yet.') }}</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach ($audits as $audit)
                                <li class="mb-3">
                                    <strong>{{ optional($audit->admin)->name ?? translate('System') }}</strong>
                                    <div class="small text-muted">{{ $audit->created_at->format('d M Y, h:i A') }} · {{ $audit->ip_address }}</div>
                                    <div class="small">
                                        {{ translate('Buyer %') }}: {{ data_get($audit->new_values, 'buyer_pct') }} · {{ translate('Seller %') }}: {{ data_get($audit->new_values, 'seller_pct') }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
