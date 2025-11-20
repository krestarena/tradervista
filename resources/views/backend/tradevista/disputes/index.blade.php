@extends('backend.layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h5 class="mb-0">{{ translate('Payment Protection Disputes') }}</h5>
            <form method="GET" class="d-flex align-items-center">
                <select name="status" class="form-control aiz-selectpicker" data-style="btn-sm" data-width="200px" onchange="this.form.submit()">
                    <option value="">{{ translate('All statuses') }}</option>
                    @foreach (['open' => translate('Open'), 'under_review' => translate('Under review'), 'resolved' => translate('Resolved')] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Order') }}</th>
                            <th>{{ translate('Customer') }}</th>
                            <th>{{ translate('Reason') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Submitted') }}</th>
                            <th class="text-right">{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($disputes as $dispute)
                            <tr>
                                <td>#{{ $dispute->id }}</td>
                                <td>
                                    <strong>{{ $dispute->order->code ?? translate('Order removed') }}</strong>
                                </td>
                                <td>{{ optional($dispute->submittedBy)->name }}</td>
                                <td>{{ Str::limit($dispute->reason, 50) }}</td>
                                <td><span class="badge badge-inline badge-secondary text-uppercase">{{ str_replace('_', ' ', $dispute->status) }}</span></td>
                                <td>{{ $dispute->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.tradevista.disputes.show', $dispute) }}" class="btn btn-soft-primary btn-sm">{{ translate('View') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">{{ translate('No disputes found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $disputes->links() }}
        </div>
    </div>
@endsection
