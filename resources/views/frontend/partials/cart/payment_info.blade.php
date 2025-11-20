<div class="mb-4">
    <h3 class="fs-16 fw-700 text-dark">
        {{ translate('Any additional info?') }}
    </h3>
    <textarea name="additional_info" rows="5" class="form-control rounded-0"
        placeholder="{{ translate('Type your text...') }}"></textarea>
</div>
<div>
    <h3 class="fs-16 fw-700 text-dark">
        {{ translate('Select a payment option') }}
    </h3>
    <div class="row gutters-10">
        @foreach (get_activate_payment_methods() as $payment_method)
            <div class="col-xl-4 col-md-6">
                <label class="aiz-megabox d-block mb-3">
                    <input value="{{ $payment_method->name }}" class="online_payment" type="radio"
                        name="payment_option" checked>
                    <span class="d-flex align-items-center justify-content-between aiz-megabox-elem rounded-0 p-3">
                        <span class="d-block fw-400 fs-14">{{ ucfirst(translate($payment_method->name)) }}</span>
                        <span class="rounded-1 h-40px overflow-hidden">
                            <img src="{{ static_asset('assets/img/cards/'.$payment_method->name.'.png') }}"
                            class="img-fit h-100">
                        </span>
                    </span>
                </label>
            </div>
        @endforeach

        <!-- Cash Payment -->
        @if (get_setting('cash_payment') == 1)
            @php
                $digital = 0;
                $cod_on = 1;
                foreach ($carts as $cartItem) {
                    $product = get_single_product($cartItem['product_id']);
                    if ($product['digital'] == 1) {
                        $digital = 1;
                    }
                    if ($product['cash_on_delivery'] == 0) {
                        $cod_on = 0;
                    }
                }
            @endphp
            @if ($digital != 1 && $cod_on == 1)
                <div class="col-xl-4 col-md-6">
                    <label class="aiz-megabox d-block mb-3">
                        <input value="cash_on_delivery" class="online_payment" type="radio"
                            name="payment_option" checked>
                        <span class="d-flex align-items-center justify-content-between aiz-megabox-elem rounded-0 p-3">
                            <span class="d-block fw-400 fs-14">{{ translate('Cash on Delivery') }}</span>
                            <span class="rounded-1 h-40px w-70px overflow-hidden">
                                <img src="{{ static_asset('assets/img/cards/cod.png') }}"
                                class="img-fit h-100">
                            </span>
                        </span>
                    </label>
                </div>
            @endif
        @endif

        @if (Auth::check())
            <!-- Offline Payment -->
            @if (addon_is_activated('offline_payment'))
                @foreach (get_all_manual_payment_methods() as $method)
                    <div class="col-xl-4 col-md-6">
                        <label class="aiz-megabox d-block mb-3">
                            <input value="{{ $method->heading }}" type="radio"
                                name="payment_option" class="offline_payment_option"
                                onchange="toggleManualPaymentData({{ $method->id }})"
                                data-id="{{ $method->id }}" checked>
                            <span class="d-flex align-items-center justify-content-between aiz-megabox-elem rounded-0 p-3">
                                <span class="d-block fw-400 fs-14">{{ $method->heading }}</span>
                                <span class="rounded-1 h-40px w-70px overflow-hidden">
                                    <img src="{{ uploaded_asset($method->photo) }}"
                                    class="img-fit h-100">
                                </span>
                            </span>
                        </label>
                    </div>
                @endforeach

                @foreach (get_all_manual_payment_methods() as $method)
                    <div id="manual_payment_info_{{ $method->id }}" class="d-none">
                        @php echo $method->description @endphp
                        @if ($method->bank_info != null)
                            <ul>
                                @foreach (json_decode($method->bank_info) as $key => $info)
                                    <li>{{ translate('Bank Name') }} -
                                        {{ $info->bank_name }},
                                        {{ translate('Account Name') }} -
                                        {{ $info->account_name }},
                                        {{ translate('Account Number') }} -
                                        {{ $info->account_number }},
                                        {{ translate('Routing Number') }} -
                                        {{ $info->routing_number }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            @endif
        @endif
    </div>

    <!-- Offline Payment Fields -->
    @if (addon_is_activated('offline_payment') && count(get_all_manual_payment_methods())>0)
        <div class="d-none mb-3 rounded border bg-white p-3 text-left">
            <div id="manual_payment_description">

            </div>
            <br>
            <div class="row">
                <div class="col-md-3">
                    <label>{{ translate('Transaction ID') }} <span
                            class="text-danger">*</span></label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control mb-3" name="trx_id" onchange="stepCompletionPaymentInfo()"
                        id="trx_id" placeholder="{{ translate('Transaction ID') }}"
                        required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">{{ translate('Photo') }}</label>
                <div class="col-md-9">
                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                        <div class="input-group-prepend">
                            <div class="input-group-text bg-soft-secondary font-weight-medium">
                                {{ translate('Browse') }}</div>
                        </div>
                        <div class="form-control file-amount">{{ translate('Choose image') }}
                        </div>
                        <input type="hidden" name="photo" class="selected-files">
                    </div>
                    <div class="file-preview box sm">
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($voucherContext) && ($voucherContext['enabled'] ?? false))
        <div class="py-4 px-4 bg-white border mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="fs-14 fw-600">{{ translate('Voucher Wallet') }}</div>
                    <small class="text-muted">{{ translate('Balance:') }} {{ single_price($voucherContext['balance']) }}</small>
                </div>
                <div class="text-right">
                    <span class="badge badge-soft-secondary">{{ translate('Locked:') }} {{ single_price($voucherContext['locked_balance']) }}</span>
                </div>
            </div>
            @if ($voucherContext['applied_amount'] > 0)
                <p class="mb-3 text-success">
                    {{ translate('Applying') }} <strong>{{ single_price($voucherContext['applied_amount']) }}</strong>
                    {{ translate('to this order.') }}
                </p>
                <form method="POST" action="{{ route('checkout.voucher.remove') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-secondary btn-sm rounded-0">{{ translate('Remove voucher') }}</button>
                </form>
            @else
                <form method="POST" action="{{ route('checkout.voucher.apply') }}" class="form-inline">
                    @csrf
                    <label class="sr-only" for="voucher_amount_inline">{{ translate('Voucher amount') }}</label>
                    <input type="number" step="0.01" min="0" max="{{ $voucherContext['balance'] }}" name="amount" id="voucher_amount_inline"
                        class="form-control rounded-0 mr-2" value="{{ $voucherContext['suggested_amount'] }}" required>
                    <button type="submit" class="btn btn-primary rounded-0">{{ translate('Apply Voucher') }}</button>
                </form>
            @endif
            <small class="d-block mt-2 text-muted">{{ translate('Voucher funds are reserved and debited after payment confirmation.') }}</small>
        </div>
    @endif

    <!-- Wallet Payment -->
    @if (Auth::check() && get_setting('wallet_system') == 1)
        <div class="py-4 px-4 text-center bg-soft-secondary-base mt-4">
            <div class="fs-14 mb-3">
                <span class="opacity-80">{{ translate('Or, Your wallet balance :') }}</span>
                <span class="fw-700">{{ single_price(Auth::user()->balance) }}</span>
            </div>
            @if (Auth::user()->balance < $total)
                <button type="button" class="btn btn-secondary" disabled>
                    {{ translate('Insufficient balance') }}
                </button>
            @else
                <button type="button" onclick="use_wallet()"
                    class="btn btn-primary fs-14 fw-700 px-5 rounded-0">
                    {{ translate('Pay with wallet') }}
                </button>
            @endif
        </div>
    @endif
</div>
