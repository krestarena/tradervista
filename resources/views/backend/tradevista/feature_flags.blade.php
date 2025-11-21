@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="fw-600 mb-0">{{ translate('TradeVista Feature Flags') }}</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.tradevista.feature-flags.update') }}" method="POST">
                    @csrf
                    <div class="row">
                        @foreach ([
                            'voucher_wallet_enabled' => translate('Voucher wallet at checkout'),
                            'own_dispatch_option_enabled' => translate('Own dispatch shipping option'),
                            'own_dispatch_modal_enabled' => translate('Own dispatch disclaimer modal'),
                            'own_dispatch_immediate_payout' => translate('Immediate payout for own dispatch'),
                            'payment_protection_badge_enabled' => translate('Payment Protection badge on orders'),
                            'click_and_collect_enabled' => translate('Click & Collect pickup windows'),
                            'click_and_collect_notification' => translate('Notify when pickup ready'),
                            'dispatcher_selection_enabled' => translate('TradeVista dispatchers at checkout'),
                            'voucher_redemption_portal_enabled' => translate('Merchant voucher redemption portal'),
                            'commission_statement_exports_enabled' => translate('Commission statement exports'),
                            'audit_logging_enabled' => translate('Audit logging for admin changes'),
                            'weekly_settlement_enabled' => translate('Weekly settlement policy'),
                        ] as $name => $label)
                            <div class="col-md-6">
                                <div class="form-group d-flex align-items-center justify-content-between border rounded p-3 mb-3">
                                    <div>
                                        <label class="mb-1 font-weight-bold" for="{{ $name }}">{{ $label }}</label>
                                    </div>
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input type="hidden" name="{{ $name }}" value="0">
                                        <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="1" {{ $settings[$name] ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ translate('Payment Protection hold (days)') }}</label>
                                <input type="number" name="payment_protection_window_days" class="form-control" min="1" max="30" value="{{ $settings['payment_protection_window_days'] }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ translate('Dispute window (hours)') }}</label>
                                <input type="number" name="dispute_window_hours" class="form-control" min="12" max="168" value="{{ $settings['dispute_window_hours'] }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ translate('OTP expiry (minutes)') }}</label>
                                <input type="number" name="otp_expiry_minutes" class="form-control" min="2" max="30" value="{{ $settings['otp_expiry_minutes'] }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ translate('OTP daily limit') }}</label>
                                <input type="number" name="otp_daily_limit" class="form-control" min="1" max="20" value="{{ $settings['otp_daily_limit'] }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">{{ translate('Save changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="fw-600 mb-0">{{ translate('Guidance') }}</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small text-muted">
                    <li class="mb-2">{{ translate('Use these toggles to turn TradeVista functionality on or off without deployments.') }}</li>
                    <li class="mb-2">{{ translate('Payment Protection and dispute timers apply to new orders only.') }}</li>
                    <li class="mb-2">{{ translate('OTP changes impact both buyer login and registration flows immediately.') }}</li>
                    <li class="mb-2">{{ translate('Voucher wallet and redemption portal must both be on for merchant redemptions to work end-to-end.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-label { font-weight: 600; }
</style>
@endpush
