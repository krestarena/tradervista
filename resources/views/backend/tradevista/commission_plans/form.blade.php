@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ $plan->exists ? translate('Edit Commission Plan') : translate('New Commission Plan') }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ $plan->exists ? route('admin.commission-plans.update', $plan) : route('admin.commission-plans.store') }}" method="POST">
            @csrf
            @if($plan->exists)
                @method('PUT')
            @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ translate('Name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{{ translate('Commission %') }}</label>
                        <input type="number" name="percentage" class="form-control" min="0" max="100" step="0.01" value="{{ old('percentage', $plan->percentage) }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{{ translate('Status') }}</label>
                        <select name="status" class="form-control aiz-selectpicker">
                            <option value="active" {{ old('status', $plan->status ?? 'active') === 'active' ? 'selected' : '' }}>{{ translate('Active') }}</option>
                            <option value="inactive" {{ old('status', $plan->status ?? 'active') === 'inactive' ? 'selected' : '' }}>{{ translate('Inactive') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{{ translate('Starts At') }}</label>
                        <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($plan->starts_at)->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{{ translate('Ends At') }}</label>
                        <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', optional($plan->ends_at)->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ translate('Categories') }}</label>
                        <select name="category_ids[]" class="form-control aiz-selectpicker" data-live-search="true" multiple>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ collect(old('category_ids', $plan->categories->pluck('id')->toArray()))->contains($category->id) ? 'selected' : '' }}>
                                    {{ $category->getTranslation('name') }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block">{{ translate('Leave empty to apply globally to uncategorized items.') }}</small>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>{{ translate('Notes') }}</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $plan->notes) }}</textarea>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">{{ translate('Save Plan') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
