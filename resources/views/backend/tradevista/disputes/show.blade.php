@extends('backend.layouts.app')

@section('content')
    <div class="row gutters-10">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ translate('Dispute #') }}{{ $dispute->id }}</h5>
                    <span class="badge badge-inline badge-secondary text-uppercase">{{ str_replace('_', ' ', $dispute->status) }}</span>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4 text-muted">{{ translate('Order code') }}</dt>
                        <dd class="col-sm-8">{{ $dispute->order->code ?? translate('Removed') }}</dd>
                        <dt class="col-sm-4 text-muted">{{ translate('Customer') }}</dt>
                        <dd class="col-sm-8">{{ optional($dispute->submittedBy)->name }}</dd>
                        <dt class="col-sm-4 text-muted">{{ translate('Reason') }}</dt>
                        <dd class="col-sm-8">{{ $dispute->reason }}</dd>
                        <dt class="col-sm-4 text-muted">{{ translate('Description') }}</dt>
                        <dd class="col-sm-8">{{ $dispute->description }}</dd>
                        <dt class="col-sm-4 text-muted">{{ translate('Submitted at') }}</dt>
                        <dd class="col-sm-8">{{ $dispute->created_at->format('d M Y, h:i A') }}</dd>
                    </dl>
                    @php
                        $evidence = collect($dispute->evidence ?? [])->map(function ($id) {
                            return \App\Models\Upload::find($id);
                        })->filter();
                    @endphp
                    @if ($evidence->isNotEmpty())
                        <hr>
                        <h6 class="mb-2">{{ translate('Evidence') }}</h6>
                        <div class="d-flex flex-wrap">
                            @foreach ($evidence as $file)
                                <a href="{{ uploaded_asset($file->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary mr-2 mb-2">
                                    {{ $file->file_original_name }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            @if ($dispute->status !== \App\Models\Dispute::STATUS_RESOLVED)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">{{ translate('Review Actions') }}</h6>
                    </div>
                    <div class="card-body">
                        @if ($dispute->status === \App\Models\Dispute::STATUS_OPEN)
                            <form method="POST" action="{{ route('admin.tradevista.disputes.start-review', $dispute) }}" class="mb-4">
                                @csrf
                                <button type="submit" class="btn btn-soft-secondary btn-block">{{ translate('Mark as Under Review') }}</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.tradevista.disputes.resolve', $dispute) }}">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">{{ translate('Resolution') }}</label>
                                <select name="resolution" class="form-control" required>
                                    <option value="">{{ translate('Select an action') }}</option>
                                    <option value="release_funds">{{ translate('Release funds to vendor') }}</option>
                                    <option value="refund">{{ translate('Full refund to buyer') }}</option>
                                    <option value="partial">{{ translate('Partial adjustment') }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ translate('Decision notes') }}</label>
                                <textarea class="form-control" name="decision_notes" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ translate('Adjustment amount (optional)') }}</label>
                                <input type="number" name="decision_amount" class="form-control" step="0.01" min="0">
                                <small class="text-muted">{{ translate('Required if selecting partial adjustment.') }}</small>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirm_resolution" name="confirm_resolution" value="1" required>
                                    <label class="form-check-label" for="confirm_resolution">{{ translate('I confirm this decision is final and will notify both parties.') }}</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">{{ translate('Resolve Dispute') }}</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">{{ translate('Resolution Summary') }}</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>{{ translate('Resolution') }}:</strong> {{ str_replace('_', ' ', $dispute->resolution) }}</p>
                        <p class="mb-2"><strong>{{ translate('Notes') }}:</strong> {{ $dispute->decision_notes }}</p>
                        <p class="mb-0"><strong>{{ translate('Resolved at') }}:</strong> {{ optional($dispute->resolved_at)->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
