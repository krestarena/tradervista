<form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="flutterwave">
    <div class="form-group row">
        <input type="hidden" name="types[]" value="FLW_PUBLIC_KEY">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('FLW_PUBLIC_KEY') }}</label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" name="FLW_PUBLIC_KEY"
                value="{{ env('FLW_PUBLIC_KEY') }}"
                placeholder="{{ translate('FLW_PUBLIC_KEY') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="FLW_SECRET_KEY">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('FLW_SECRET_KEY') }}</label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" name="FLW_SECRET_KEY"
                value="{{ env('FLW_SECRET_KEY') }}"
                placeholder="{{ translate('FLW_SECRET_KEY') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="FLW_SECRET_HASH">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('FLW_SECRET_HASH') }}</label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" name="FLW_SECRET_HASH"
                value="{{ env('FLW_SECRET_HASH') }}"
                placeholder="{{ translate('FLW_SECRET_HASH') }}" required>
        </div>
    </div>
    <div class="form-group row">
        <input type="hidden" name="types[]" value="FLW_PAYMENT_CURRENCY_CODE">
        <div class="col-lg-4">
            <label class="col-from-label">{{ translate('FLW_PAYMENT_CURRENCY_CODE') }}</label>
        </div>
        <div class="col-lg-8">
            <input type="text" class="form-control" name="FLW_PAYMENT_CURRENCY_CODE"
                value="{{ env('FLW_PAYMENT_CURRENCY_CODE') }}"
                placeholder="{{ translate('FLW_PAYMENT_CURRENCY_CODE') }}" required>
        </div>
    </div>

    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
    </div>
</form>