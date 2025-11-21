@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0">{{ translate('TradeVista Reports') }}</h5>
            <small class="text-muted">{{ translate('Operational snapshots for Payment Protection, vouchers, and referrals') }}</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.tradevista.reports.voucher-liability') }}" class="btn btn-soft-primary btn-sm">
                {{ translate('Export Voucher Liability') }}
            </a>
            <a href="{{ route('admin.tradevista.reports.commission-holds') }}" class="btn btn-soft-secondary btn-sm">
                {{ translate('Export Commission Holds') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row gutters-10">
            <div class="col-lg-4 mb-3">
                <div class="bg-soft-primary p-3 rounded h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-16 fw-700">{{ format_price($gmvLast30) }}</div>
                            <div class="fs-13 text-muted">{{ translate('GMV (last 30 days)') }}</div>
                        </div>
                        <span class="badge badge-inline badge-primary">{{ translate('GMV') }}</span>
                    </div>
                    <div class="fs-13 mt-2 text-secondary">{{ translate('Last 7 days:') }} {{ format_price($gmvLast7) }}</div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="bg-soft-info p-3 rounded h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-16 fw-700">{{ $voucherLiability > 0 ? format_price($voucherLiability) : translate('No balance') }}</div>
                            <div class="fs-13 text-muted">{{ translate('Voucher liability') }}</div>
                        </div>
                        <span class="badge badge-inline badge-info">{{ translate('Vouchers') }}</span>
                    </div>
                    <div class="fs-13 mt-2 text-secondary">{{ translate('Pending referral jobs:') }} {{ $pendingReferralJobs }}</div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="bg-soft-warning p-3 rounded h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-16 fw-700">{{ $heldOrdersCount }}</div>
                            <div class="fs-13 text-muted">{{ translate('Orders in Payment Protection') }}</div>
                        </div>
                        <span class="badge badge-inline badge-warning">{{ translate('Holds') }}</span>
                    </div>
                    <div class="fs-13 mt-2 text-secondary">{{ translate('Commission records on hold:') }} {{ $commissionHeld }}</div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="bg-soft-danger p-3 rounded h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-16 fw-700">{{ $openDisputes }}</div>
                            <div class="fs-13 text-muted">{{ translate('Open disputes') }}</div>
                        </div>
                        <span class="badge badge-inline badge-danger">{{ translate('Disputes') }}</span>
                    </div>
                    <div class="fs-13 mt-2 text-secondary">{{ translate('Use the dispute console to resolve and release holds.') }}</div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="bg-soft-secondary p-3 rounded h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-16 fw-700">{{ $pendingReferralJobs }}</div>
                            <div class="fs-13 text-muted">{{ translate('Pending referral jobs') }}</div>
                        </div>
                        <span class="badge badge-inline badge-secondary">{{ translate('Referrals') }}</span>
                    </div>
                    <div class="fs-13 mt-2 text-secondary">{{ translate('Referral payouts queue after Payment Protection releases.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
