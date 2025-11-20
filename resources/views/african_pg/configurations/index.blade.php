@extends('backend.layouts.app')

@section('content')

    <div class="row">
        @if (addon_is_activated('african_pg'))
            @if (get_setting('mpesa') == 1)
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <img class="mr-3" src="{{ static_asset('assets/img/cards/mpesa.png') }}" height="30">
                                <h5 class="mb-0 h6">{{ translate('Mpesa Credential') }}</h5>
                            </div>
                            <label class="aiz-switch aiz-switch-success mb-0 float-right">
                                <input type="checkbox" onchange="updateSettings(this, 'mpesa')" @if (get_setting('mpesa') == 1) checked @endif>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="card-body">
                            <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="payment_method" value="mpesa">
                                <div class="form-group row">
                                    <input type="hidden" name="types[]" value="MPESA_CONSUMER_KEY">
                                    <div class="col-lg-4">
                                        <label class="col-from-label">{{ translate('MPESA CONSUMER KEY') }}</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" class="form-control" name="MPESA_CONSUMER_KEY"
                                            value="{{ env('MPESA_CONSUMER_KEY') }}"
                                            placeholder="{{ translate('MPESA_CONSUMER_KEY') }}" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <input type="hidden" name="types[]" value="MPESA_CONSUMER_SECRET">
                                    <div class="col-lg-4">
                                        <label class="col-from-label">{{ translate('MPESA CONSUMER SECRET') }}</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" class="form-control" name="MPESA_CONSUMER_SECRET"
                                            value="{{ env('MPESA_CONSUMER_SECRET') }}"
                                            placeholder="{{ translate('MPESA_CONSUMER_SECRET') }}" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <input type="hidden" name="types[]" value="MPESA_SHORT_CODE">
                                    <div class="col-lg-4">
                                        <label class="col-from-label">{{ translate('MPESA SHORT CODE') }}</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" class="form-control" name="MPESA_SHORT_CODE"
                                            value="{{ env('MPESA_SHORT_CODE') }}"
                                            placeholder="{{ translate('MPESA_SHORT_CODE') }}" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <input type="hidden" name="types[]" value="MPESA_USERNAME">
                                    <div class="col-lg-4">
                                        <label class="col-from-label">{{ translate('MPESA USERNAME') }}</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" class="form-control" name="MPESA_USERNAME"
                                            value="{{ env('MPESA_USERNAME') }}"
                                            placeholder="{{ translate('MPESA_USERNAME') }}" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <input type="hidden" name="types[]" value="MPESA_PASSWORD">
                                    <div class="col-lg-4">
                                        <label class="col-from-label">{{ translate('MPESA PASSWORD') }}</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" class="form-control" name="MPESA_PASSWORD"
                                            value="{{ env('MPESA_PASSWORD') }}"
                                            placeholder="{{ translate('MPESA_PASSWORD') }}" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <input type="hidden" name="types[]" value="MPESA_PASSKEY">
                                    <div class="col-lg-4">
                                        <label class="col-from-label">{{ translate('MPESA PASSKEY') }}</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" class="form-control" name="MPESA_PASSKEY"
                                            value="{{ env('MPESA_PASSKEY') }}"
                                            placeholder="{{ translate('MPESA_PASSKEY') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <input type="hidden" name="types[]" value="MPESA_ENV">
                                    <div class="col-lg-4">
                                        <label class="col-from-label">{{ translate('MPESA SANDBOX ACTIVATION') }}</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <select name="MPESA_ENV" class="form-control aiz-selectpicker" required>
                                            <option value="live" @if (env('MPESA_ENV') == 'live') selected @endif>
                                                {{ translate('Live') }}</option>
                                            <option value="sandbox" @if (env('MPESA_ENV') == 'sandbox') selected @endif>
                                                {{ translate('Sandbox') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group mb-0 text-right">
                                    <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            @foreach ($payment_methods as $payment_method)
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <img class="mr-3" src="{{ static_asset('assets/img/cards/'.$payment_method->name.'.png') }}" height="30">
                                <h5 class="mb-0 h6">{{ ucfirst(translate($payment_method->name)) }}</h5>
                            </div>
                            <label class="aiz-switch aiz-switch-success mb-0 float-right">
                                <input type="checkbox" onchange="updatePaymentSettings(this, {{ $payment_method->id }})" @if ($payment_method->active == 1) checked @endif>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="card-body">
                            @include('african_pg.configurations.payment_method.'.$payment_method->name)
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function updatePaymentSettings(el, id) {
            if ($(el).is(':checked')) {
                var value = 1;
            } else {
                var value = 0;
            }

            $.post('{{ route('payment.activation') }}', {
                _token: '{{ csrf_token() }}',
                id: id,
                value: value
            }, function(data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', '{{ translate('Payment Settings updated successfully') }}');
                } else {
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
        
        function updateSettings(el, type) {
            if ($(el).is(':checked')) {
                var value = 1;
            } else {
                var value = 0;
            }
            $.post('{{ route('business_settings.update.activation') }}', {
                _token: '{{ csrf_token() }}',
                type: type,
                value: value
            }, function(data) {
                if (data == '1') {
                    AIZ.plugins.notify('success', 'Settings updated successfully');
                } else {
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
    </script>
@endsection
