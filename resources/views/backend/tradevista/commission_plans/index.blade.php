@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 h6">{{ translate('Commission Plans') }}</h5>
        <a href="{{ route('admin.commission-plans.create') }}" class="btn btn-primary">{{ translate('Add Plan') }}</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ translate('Name') }}</th>
                        <th>{{ translate('Percentage') }}</th>
                        <th>{{ translate('Categories') }}</th>
                        <th>{{ translate('Window') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th class="text-right">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plans as $plan)
                        <tr>
                            <td>{{ $loop->iteration + ($plans->currentPage() - 1) * $plans->perPage() }}</td>
                            <td>{{ $plan->name }}</td>
                            <td>{{ $plan->percentage }}%</td>
                            <td>
                                <span class="badge badge-inline badge-secondary">{{ $plan->categories->count() }}</span>
                                @foreach($plan->categories as $category)
                                    <span class="badge badge-inline badge-light">{{ $category->getTranslation('name') }}</span>
                                @endforeach
                            </td>
                            <td>
                                <div class="small text-muted">
                                    {{ $plan->starts_at ? $plan->starts_at->format('M j, Y H:i') : translate('Immediately') }}
                                    —
                                    {{ $plan->ends_at ? $plan->ends_at->format('M j, Y H:i') : translate('No end') }}
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-inline badge-{{ $plan->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($plan->status) }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.commission-plans.edit', $plan) }}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ translate('Edit') }}">
                                    <i class="las la-pen"></i>
                                </a>
                                <form action="{{ route('admin.commission-plans.destroy', $plan) }}" method="POST" class="d-inline-block" onsubmit="return confirm('{{ translate('Delete this plan?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-icon btn-circle btn-sm" title="{{ translate('Delete') }}">
                                        <i class="las la-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">{{ translate('No commission plans found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="clearfix mt-3">
            {{ $plans->links() }}
        </div>
    </div>
</div>
@endsection
