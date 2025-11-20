@extends('delivery_boys.layouts.app')

@section('panel_content')
    <div class="aiz-titlebar mb-4">
      <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="fs-20 fw-700 text-dark">{{ translate('Manage Profile') }}</h1>
        </div>
      </div>
    </div>

    @php
        $dispatcherProfile = \App\Models\DeliveryBoy::firstOrNew(['user_id' => Auth::user()->id]);
        $serviceCities = \App\Models\City::where('status', 1)->orderBy('name')->get();
        $documentTypes = config('tradevista.dispatcher.document_types');
        $selectedAreas = collect(old('service_area_cities', $dispatcherProfile->service_area_cities ?? []));
    @endphp

    <!-- Basic Info -->
    <div class="card shadow-none rounded-0 border">
        <div class="card-header border-bottom-0">
            <h5 class="mb-0 fs-16 fw-700 text-dark">{{ translate('Basic Info')}}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Name -->
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">{{ translate('Your Name') }}</label>
                    <div class="col-md-10">
                        <input type="text" class="form-control rounded-0" placeholder="{{ translate('Your Name') }}" name="name" value="{{ Auth::user()->name }}">
                    </div>
                </div>
                <!-- Phone -->
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">{{ translate('Your Phone') }}</label>
                    <div class="col-md-10">
                        <input type="text" class="form-control rounded-0" placeholder="{{ translate('Your Phone')}}" name="phone" value="{{ Auth::user()->phone }}">
                    </div>
                </div>
                <!-- Photo -->
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">{{ translate('Photo') }}</label>
                    <div class="col-md-10">
                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium rounded-0">{{ translate('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                            <input type="hidden" name="photo" value="{{ Auth::user()->avatar_original }}" class="selected-files">
                        </div>
                        <div class="file-preview box sm">
                        </div>
                    </div>
                </div>
                <!-- Password -->
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">{{ translate('Your Password') }}</label>
                    <div class="col-md-10">
                        <input type="password" class="form-control rounded-0" placeholder="{{ translate('New Password') }}" name="new_password">
                    </div>
                </div>
                <!-- Confirm Password -->
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">{{ translate('Confirm Password') }}</label>
                    <div class="col-md-10">
                        <input type="password" class="form-control rounded-0" placeholder="{{ translate('Confirm Password') }}" name="confirm_password">
                    </div>
                </div>
                <!-- Address -->
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">{{ translate('Your Address') }}</label>
                    <div class="col-md-10">
                        <textarea class="form-control rounded-0 mb-3" placeholder="{{ translate('Your Address') }}" rows="3" name="address" required>{{ Auth::user()->address }}</textarea>
                    </div>
                </div>
                <!-- Update Profile Button -->
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary rounded-0 w-150px">{{translate('Update Profile')}}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-none rounded-0 border">
        <div class="card-header border-bottom-0">
            <h5 class="mb-0 fs-16 fw-700 text-dark">{{ translate('TradeVista Dispatcher KYC')}}</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                @php $status = $dispatcherProfile->kyc_status ?? \App\Models\DeliveryBoy::KYC_STATUS_DRAFT; @endphp
                <span class="badge badge-@if($status === \App\Models\DeliveryBoy::KYC_STATUS_APPROVED) success @elseif($status === \App\Models\DeliveryBoy::KYC_STATUS_REJECTED) danger @else warning @endif">
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </span>
                @if($dispatcherProfile->kyc_rejection_reason)
                    <small class="text-danger d-block">{{ $dispatcherProfile->kyc_rejection_reason }}</small>
                @elseif($status === \App\Models\DeliveryBoy::KYC_STATUS_APPROVED)
                    <small class="text-success d-block">{{ translate('You can now be assigned to new orders that match your service areas.') }}</small>
                @else
                    <small class="text-muted d-block">{{ translate('Complete the form below to be selectable at checkout.') }}</small>
                @endif
            </div>

            <form action="{{ route('delivery-boy.kyc.submit') }}" method="POST">
                @csrf
                <div class="form-group row">
                    <label class="col-md-3 col-form-label">{{ translate('Document Type') }}</label>
                    <div class="col-md-9">
                        <select class="form-control aiz-selectpicker" name="kyc_document_type" data-live-search="true" required>
                            <option value="">{{ translate('Select document type') }}</option>
                            @foreach($documentTypes as $docType)
                                <option value="{{ $docType }}" @if(old('kyc_document_type', $dispatcherProfile->kyc_document_type) == $docType) selected @endif>
                                    {{ ucfirst(str_replace('_', ' ', $docType)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label">{{ translate('Document Number') }}</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="kyc_document_number" value="{{ old('kyc_document_number', $dispatcherProfile->kyc_document_number) }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label">{{ translate('Document Upload') }}</label>
                    <div class="col-md-9">
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                            <input type="hidden" name="kyc_document_upload_id" class="selected-files" value="{{ old('kyc_document_upload_id', $dispatcherProfile->kyc_document_upload_id) }}">
                        </div>
                        <div class="file-preview box sm"></div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label">{{ translate('License Number') }}</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="license_number" value="{{ old('license_number', $dispatcherProfile->license_number) }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label">{{ translate('Service Areas') }}</label>
                    <div class="col-md-9">
                        <select class="form-control aiz-selectpicker" name="service_area_cities[]" multiple data-live-search="true" required>
                            @foreach($serviceCities as $city)
                                <option value="{{ $city->id }}" @if($selectedAreas->contains($city->id)) selected @endif>{{ $city->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ translate('Only approved cities will make you eligible for those routes.') }}</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label">{{ translate('Base Rate (₦)') }}</label>
                    <div class="col-md-9">
                        <input type="number" min="0" step="0.01" class="form-control" name="default_rate" value="{{ old('default_rate', $dispatcherProfile->default_rate) }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label">{{ translate('ETA (hours)') }}</label>
                    <div class="col-md-9">
                        <input type="number" min="1" class="form-control" name="default_eta_hours" value="{{ old('default_eta_hours', $dispatcherProfile->default_eta_hours ?? config('tradevista.dispatcher.default_eta_hours')) }}" required>
                    </div>
                </div>

                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary rounded-0 w-150px">{{ translate('Submit KYC') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Change -->
    <div class="card shadow-none rounded-0 border">
        <div class="card-header border-bottom-0">
            <h5 class="mb-0 fs-16 fw-700 text-dark">{{ translate('Change your email')}}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('user.change.email') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-2">
                        <label>{{ translate('Your Email') }}</label>
                    </div>
                    <div class="col-md-10">
                        <!-- Email -->
                        <div class="input-group mb-3">
                          <input type="email" class="form-control rounded-0" placeholder="{{ translate('Your Email')}}" name="email" value="{{ Auth::user()->email }}" />
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary new-email-verification rounded-0">
                                    <span class="d-none loading">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        {{ translate('Sending Email...') }}
                                    </span>
                                    <span class="default">{{ translate('Verify') }}</span>
                                </button>
                            </div>
                        </div>
                        <!-- Update Email Button -->
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-primary rounded-0 w-150px">{{translate('Update Email')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        (function($) {
            "use strict";
        
            $('.new-email-verification').on('click', function() {
                $(this).find('.loading').removeClass('d-none');
                $(this).find('.default').addClass('d-none');
                var email = $("input[name=email]").val();
        
                $.post('{{ route('user.new.verify') }}', {_token:'{{ csrf_token() }}', email: email}, function(data){
                    data = JSON.parse(data);
                    $('.default').removeClass('d-none');
                    $('.loading').addClass('d-none');
                    if(data.status == 2)
                        AIZ.plugins.notify('warning', data.message);
                    else if(data.status == 1)
                        AIZ.plugins.notify('success', data.message);
                    else
                        AIZ.plugins.notify('danger', data.message);
                });
            });
        })(jQuery);
    </script>
@endsection
