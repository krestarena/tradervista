@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="align-items-center">
            <h1 class="h3">{{ translate('Review Vendor KYC') }}</h1>
            <p class="mb-0 text-muted">{{ translate('Verify documents before enabling the public badge.') }}</p>
        </div>
    </div>

    @php
        $documents = $submission->documents ?? [];
        $bank = $documents['bank'] ?? [];
        $storePhotos = $documents['store_photos'] ?? [];
    @endphp

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="mb-1">{{ translate('Vendor') }}</h5>
                    <div class="fw-600">{{ optional($submission->user)->name ?? translate('Unknown') }}</div>
                    <div class="text-muted small">{{ optional($submission->user)->email ?? translate('Not provided') }}</div>
                    <div class="text-muted small">{{ optional($submission->user)->phone ?? translate('Not provided') }}</div>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-1">{{ translate('Status') }}</h5>
                    @if ($submission->status === 'approved')
                        <span class="badge badge-inline badge-success">{{ translate('Approved') }}</span>
                    @elseif($submission->status === 'rejected')
                        <span class="badge badge-inline badge-danger">{{ translate('Rejected') }}</span>
                    @else
                        <span class="badge badge-inline badge-warning">{{ translate('Pending') }}</span>
                    @endif
                    <p class="text-muted small mt-2">{{ translate('Submitted at') }}: {{ $submission->created_at?->format('M d, Y h:i A') }}</p>
                    @if ($submission->reviewed_at)
                        <p class="text-muted small mb-0">{{ translate('Reviewed at') }}: {{ $submission->reviewed_at->format('M d, Y h:i A') }}</p>
                    @endif
                </div>
                <div class="col-md-4">
                    <h5 class="mb-1">{{ translate('Rejection Notes') }}</h5>
                    @if (!empty($submission->rejection_notes['reason']))
                        <p class="mb-0">{{ $submission->rejection_notes['reason'] }}</p>
                    @else
                        <p class="text-muted mb-0">{{ translate('No notes yet.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header border-0">
            <h5 class="mb-0">{{ translate('Identity Information') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted small mb-1">{{ translate('ID Type') }}</p>
                    <p class="fw-600">{{ $documents['id_type'] ?? translate('N/A') }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">{{ translate('ID Number') }}</p>
                    <p class="fw-600">{{ $documents['id_number'] ?? translate('N/A') }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">{{ translate('CAC Number') }}</p>
                    <p class="fw-600">{{ $documents['cac_number'] ?? translate('Not provided') }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">{{ translate('ID Document') }}</p>
                    @if (!empty($documents['id_document']))
                        <a href="{{ uploaded_asset($documents['id_document']) }}" target="_blank">{{ translate('View document') }}</a>
                    @else
                        <span class="text-muted">{{ translate('Not uploaded') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header border-0">
            <h5 class="mb-0">{{ translate('Bank & Settlement Details') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p class="text-muted small mb-1">{{ translate('Bank Name') }}</p>
                    <p class="fw-600">{{ $bank['name'] ?? translate('N/A') }}</p>
                </div>
                <div class="col-md-4">
                    <p class="text-muted small mb-1">{{ translate('Account Name') }}</p>
                    <p class="fw-600">{{ $bank['account_name'] ?? translate('N/A') }}</p>
                </div>
                <div class="col-md-4">
                    <p class="text-muted small mb-1">{{ translate('Account Number') }}</p>
                    <p class="fw-600">{{ $bank['account_number'] ?? translate('N/A') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header border-0">
            <h5 class="mb-0">{{ translate('Store Photos') }}</h5>
        </div>
        <div class="card-body">
            @if (!empty($storePhotos))
                <div class="row g-3">
                    @foreach ($storePhotos as $photo)
                        <div class="col-6 col-md-3">
                            <a href="{{ uploaded_asset($photo) }}" target="_blank">
                                <img src="{{ uploaded_asset($photo) }}" class="img-fluid rounded border" alt="store photo">
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">{{ translate('No store photos uploaded.') }}</p>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>{{ translate('Approve Application') }}</h5>
                    <p class="text-muted">{{ translate('Approve to enable the Verified Vendor badge and unlock publishing rights.') }}</p>
                    <form action="{{ route('admin.vendor-kyc.approve', $submission->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success" @if($submission->status === 'approved') disabled @endif>{{ translate('Approve & Badge Vendor') }}</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>{{ translate('Reject / Request Changes') }}</h5>
                    <form action="{{ route('admin.vendor-kyc.reject', $submission->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="reason" class="form-label">{{ translate('Reason') }}</label>
                            <textarea name="reason" id="reason" rows="3" class="form-control" required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-danger">{{ translate('Send Rejection') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
