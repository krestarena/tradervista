@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('TradeVista Highlights') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.tradevista.highlights.update') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-4 col-from-label">{{ translate('Enable highlights') }}</label>
                        <div class="col-sm-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="highlight_enabled" value="1" {{ $settings['highlight_enabled'] ? 'checked' : '' }}>
                                <span></span>
                            </label>
                            <small class="form-text text-muted">{{ translate('Toggles badge rendering for highlighted vendors/products.') }}</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-from-label">{{ translate('Active window (start)') }}</label>
                        <div class="col-sm-8">
                            <input type="datetime-local" name="highlight_start_at" class="form-control" value="{{ $settings['highlight_start_at'] ? \Carbon\Carbon::parse($settings['highlight_start_at'])->format('Y-m-d\TH:i') : '' }}">
                            <small class="form-text text-muted">{{ translate('Leave blank to start immediately.') }}</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-from-label">{{ translate('Active window (end)') }}</label>
                        <div class="col-sm-8">
                            <input type="datetime-local" name="highlight_end_at" class="form-control" value="{{ $settings['highlight_end_at'] ? \Carbon\Carbon::parse($settings['highlight_end_at'])->format('Y-m-d\TH:i') : '' }}">
                            <small class="form-text text-muted">{{ translate('Leave blank for an open-ended highlight.') }}</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-from-label">{{ translate('Highlighted vendor IDs') }}</label>
                        <div class="col-sm-8">
                            <input type="text" name="highlight_vendors" class="form-control" value="{{ $settings['highlight_vendors'] }}" placeholder="e.g. 5, 12, 48">
                            <small class="form-text text-muted">{{ translate('Comma-separated seller user IDs that should surface highlight badges.') }}</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-from-label">{{ translate('Highlighted product IDs') }}</label>
                        <div class="col-sm-8">
                            <input type="text" name="highlight_products" class="form-control" value="{{ $settings['highlight_products'] }}" placeholder="e.g. 10, 23, 91">
                            <small class="form-text text-muted">{{ translate('Comma-separated product IDs that will show highlight badges on PDPs.') }}</small>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">{{ translate('Save highlights') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
