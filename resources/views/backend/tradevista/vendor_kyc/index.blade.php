@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="align-items-center">
            <h1 class="h3">{{ translate('Vendor KYC Submissions') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="row g-3" method="GET">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="{{ translate('Search by vendor name, email or phone') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-control aiz-selectpicker" name="status">
                        <option value="">{{ translate('All Statuses') }}</option>
                        @foreach (['pending' => translate('Pending'), 'approved' => translate('Approved'), 'rejected' => translate('Rejected')] as $key => $label)
                            <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">{{ translate('Filter') }}</button>
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="{{ route('admin.vendor-kyc.index') }}" class="btn btn-soft-secondary w-100">{{ translate('Reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Vendor') }}</th>
                            <th>{{ translate('ID Type') }}</th>
                            <th>{{ translate('Submitted At') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th class="text-right">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissions as $submission)
                            @php $documents = $submission->documents ?? []; @endphp
                            <tr>
                                <td>{{ $submission->id }}</td>
                                <td>
                                    <div class="fw-600">{{ optional($submission->user)->name ?? translate('Unknown') }}</div>
                                    <div class="text-muted small">{{ optional($submission->user)->email ?? translate('Not provided') }}</div>
                                    <div class="text-muted small">{{ optional($submission->user)->phone ?? translate('Not provided') }}</div>
                                </td>
                                <td>{{ $documents['id_type'] ?? translate('N/A') }}</td>
                                <td>{{ $submission->created_at?->format('M d, Y h:i A') }}</td>
                                <td>
                                    @if ($submission->status === 'approved')
                                        <span class="badge badge-inline badge-success">{{ translate('Approved') }}</span>
                                    @elseif($submission->status === 'rejected')
                                        <span class="badge badge-inline badge-danger">{{ translate('Rejected') }}</span>
                                    @else
                                        <span class="badge badge-inline badge-warning">{{ translate('Pending') }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.vendor-kyc.show', $submission->id) }}" class="btn btn-soft-primary btn-sm">{{ translate('Review') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">{{ translate('No submissions found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-3">
                {{ $submissions->links() }}
            </div>
        </div>
    </div>
@endsection
