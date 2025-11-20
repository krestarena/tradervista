@extends('seller.layouts.app')

@section('panel_content')
    <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 mb-0">{{ translate('Vendor KYC & Verification') }}</h1>
            </div>
            <div class="col-md-6 text-md-right mt-3 mt-md-0">
                <a href="{{ route('shop.visit', $shop->slug) }}" class="btn btn-soft-secondary btn-sm" target="_blank">
                    {{ translate('View Shop') }} <i class="las la-external-link-alt ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    @php
        $documents = $submission->documents ?? [];
        $bank = $documents['bank'] ?? [];
        $status = $submission->status ?? null;
    @endphp

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('seller.shop.verify.store') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="mb-0 h6">{{ translate('Identity Details') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="id_type">{{ translate('Government ID Type') }} <span class="text-danger">*</span></label>
                            <select name="id_type" id="id_type" class="form-control aiz-selectpicker" data-live-search="true" required>
                                @foreach (['NIN', 'BVN', 'Passport', "Driver's License", "Voter's Card"] as $type)
                                    <option value="{{ $type }}" @selected(old('id_type', $documents['id_type'] ?? '') == $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('id_type')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="id_number">{{ translate('ID Number') }} <span class="text-danger">*</span></label>
                            <input type="text" id="id_number" name="id_number" class="form-control" value="{{ old('id_number', $documents['id_number'] ?? '') }}" required>
                            @error('id_number')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Upload ID Document') }} <span class="text-danger">*</span></label>
                            <div class="input-group" data-toggle="aizuploader" data-type="document">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose file') }}</div>
                                <input type="hidden" name="id_document" class="selected-files" value="{{ old('id_document', $documents['id_document'] ?? '') }}">
                            </div>
                            <div class="file-preview box sm"></div>
                            @error('id_document')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="cac_number">{{ translate('CAC Number (optional)') }}</label>
                            <input type="text" id="cac_number" name="cac_number" class="form-control" value="{{ old('cac_number', $documents['cac_number'] ?? '') }}">
                            @error('cac_number')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header border-0">
                        <h4 class="mb-0 h6">{{ translate('Bank & Settlement Details') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="bank_name">{{ translate('Bank Name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="bank_name" name="bank_name" class="form-control" value="{{ old('bank_name', $bank['name'] ?? '') }}" required>
                            @error('bank_name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="bank_account_name">{{ translate('Account Name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="bank_account_name" name="bank_account_name" class="form-control" value="{{ old('bank_account_name', $bank['account_name'] ?? '') }}" required>
                            @error('bank_account_name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="bank_account_number">{{ translate('Account Number') }} <span class="text-danger">*</span></label>
                            <input type="text" id="bank_account_number" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $bank['account_number'] ?? '') }}" required>
                            @error('bank_account_number')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header border-0">
                        <h4 class="mb-0 h6">{{ translate('Store Evidence') }}</h4>
                    </div>
                    <div class="card-body">
                        <label class="form-label">{{ translate('Upload Store Photos (interior/exterior)') }}</label>
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                            </div>
                            <div class="form-control file-amount">{{ translate('Choose files') }}</div>
                            <input type="hidden" name="store_photos" class="selected-files" value="{{ old('store_photos', isset($documents['store_photos']) ? implode(',', $documents['store_photos']) : '') }}">
                        </div>
                        <div class="file-preview box sm"></div>
                        @error('store_photos')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">{{ translate('Submit KYC') }}</button>
                </div>
            </form>
        </div>
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="mb-3">{{ translate('Verification Status') }}</h5>
                    @if ($status === 'approved')
                        <span class="badge badge-success text-uppercase">{{ translate('Approved') }}</span>
                        <p class="mt-3 text-muted">{{ translate('Your documents are verified. You can publish products freely.') }}</p>
                    @elseif($status === 'rejected')
                        <span class="badge badge-danger text-uppercase">{{ translate('Rejected') }}</span>
                        <p class="mt-3 text-muted">{{ translate('Update the requested fields below and resubmit to continue selling.') }}</p>
                        @if (!empty($submission->rejection_notes['reason']))
                            <div class="alert alert-soft-danger mt-3">
                                <strong>{{ translate('Reviewer note:') }}</strong>
                                <div class="mt-2">{{ $submission->rejection_notes['reason'] }}</div>
                            </div>
                        @endif
                    @elseif($status === 'pending')
                        <span class="badge badge-warning text-uppercase">{{ translate('Pending Review') }}</span>
                        <p class="mt-3 text-muted">{{ translate('Our compliance team is reviewing your submission. You can still update the form if something changed.') }}</p>
                    @else
                        <span class="badge badge-secondary text-uppercase">{{ translate('Not Submitted') }}</span>
                        <p class="mt-3 text-muted">{{ translate('Complete the form to unlock verified seller benefits and publish listings.') }}</p>
                    @endif
                    <hr>
                    <p class="text-muted small mb-1">{{ translate('Last updated') }}</p>
                    <p class="fw-600">{{ optional($submission->updated_at ?? $submission->created_at)->format('M d, Y h:i A') ?? translate('Never') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
