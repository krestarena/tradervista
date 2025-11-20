@extends('frontend.layouts.user_panel')

@section('panel_content')
    <!-- Order id -->
    <div class="aiz-titlebar mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="fs-20 fw-700 text-dark">{{ translate('Order id') }}: {{ $order->code }}</h1>
            </div>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="card rounded-0 shadow-none border mb-4">
        <div class="card-header border-bottom-0">
            <h5 class="fs-16 fw-700 text-dark mb-0">{{ translate('Order Summary') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">

                <div class="col-lg-6">
                    <table class="table-borderless table">
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Order Code') }}:</td>
                            <td>{{ $order->code }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Customer') }}:</td>
                            <td>{{ json_decode($order->shipping_address)->name }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Email') }}:</td>
                            @if ($order->user_id != null)
                                <td>{{ $order->user->email }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Shipping address') }}:</td>
                            <td>{{ json_decode($order->shipping_address)->address }},
                                {{ json_decode($order->shipping_address)->city }},
                                @if(isset(json_decode($order->shipping_address)->state)) {{ json_decode($order->shipping_address)->state }} - @endif
                                {{ json_decode($order->shipping_address)->postal_code }},
                                {{ json_decode($order->shipping_address)->country }}
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-lg-6">
                    <table class="table-borderless table">
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Order date') }}:</td>
                            <td>{{ date('d-m-Y H:i A', $order->date) }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Order status') }}:</td>
                            <td>{{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Total order amount') }}:</td>
                            <td>{{ single_price($order->orderDetails->sum('price') + $order->orderDetails->sum('tax')) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Shipping method') }}:</td>
                            <td>{{ translate('Flat shipping rate') }}</td>
                        </tr>
                        <tr>
                            <td class="w-50 fw-600">{{ translate('Payment method') }}:</td>
                            <td>{{ ucfirst(translate(str_replace('_', ' ', $order->payment_type))) }}</td>
                        </tr>
                        <tr>
                            <td class="text-main text-bold">{{ translate('Additional Info') }}</td>
                            <td class="">{{ $order->additional_info }}</td>
                        </tr>
                        @if ($order->tracking_code)
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Tracking code') }}:</td>
                                <td>{{ $order->tracking_code }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if ($order->shipping_type === 'pickup_point')
        @php
            $pickupReady = optional($order->pickup_ready_at)->format('d M Y, h:i A') ?? translate('Pending');
            $pickupEnds = optional($order->pickup_window_end)->format('d M Y, h:i A') ?? translate('Pending');
            $protectionLabel = match ($order->payment_protection_status) {
                App\Models\Order::PAYMENT_PROTECTION_RELEASED => translate('Payment Protection released'),
                App\Models\Order::PAYMENT_PROTECTION_ACTIVE => translate('Payment Protection active'),
                default => translate('Payment Protection queued'),
            };
        @endphp
        <div class="alert alert-soft-primary rounded-0 d-flex flex-column flex-lg-row justify-content-between align-items-start">
            <div>
                <h6 class="mb-1 fw-700">{{ translate('Pickup Instructions') }}</h6>
                <p class="mb-0 small text-muted">{{ translate('Collect your order within the window below to keep Payment Protection in place.') }}</p>
            </div>
            <div class="mt-3 mt-lg-0 text-left text-lg-right">
                <div class="small">{{ translate('Ready at') }}: <strong>{{ $pickupReady }}</strong></div>
                <div class="small">{{ translate('Window ends') }}: <strong>{{ $pickupEnds }}</strong></div>
                <div class="small"><strong>{{ $protectionLabel }}</strong></div>
            </div>
        </div>
    @endif

    <!-- Order Details -->
    <div class="row gutters-16">
        <div class="col-md-9">
            <div class="card rounded-0 shadow-none border mt-2 mb-4">
                <div class="card-header border-bottom-0">
                    <h5 class="fs-16 fw-700 text-dark mb-0">{{ translate('Order Details') }}</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="aiz-table table">
                        <thead class="text-gray fs-12">
                            <tr>
                                <th class="pl-0">#</th>
                                <th width="30%">{{ translate('Product') }}</th>
                                <th data-breakpoints="md">{{ translate('Variation') }}</th>
                                <th>{{ translate('Quantity') }}</th>
                                <th data-breakpoints="md">{{ translate('Delivery Type') }}</th>
                                <th>{{ translate('Price') }}</th>
                                @if (addon_is_activated('refund_request'))
                                    <th data-breakpoints="md">{{ translate('Refund') }}</th>
                                @endif
                                <th data-breakpoints="md" class="text-right pr-0">{{ translate('Review') }}</th>
                            </tr>
                        </thead>
                        <tbody class="fs-14">
                            @foreach ($order->orderDetails as $key => $orderDetail)
                                <tr>
                                    <td class="pl-0">{{ sprintf('%02d', $key+1) }}</td>
                                    <td>
                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                            <a href="{{ route('product', $orderDetail->product->slug) }}"
                                                target="_blank">{{ $orderDetail->product->getTranslation('name') }}</a>
                                        @elseif($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <a href="{{ route('auction-product', $orderDetail->product->slug) }}"
                                                target="_blank">{{ $orderDetail->product->getTranslation('name') }}</a>
                                        @else
                                            <strong>{{ translate('Product Unavailable') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $orderDetail->variation }}
                                    </td>
                                    <td>
                                        {{ $orderDetail->quantity }}
                                    </td>
                                    <td>
                                        @if ($order->shipping_type != null && $order->shipping_type == 'home_delivery')
                                            {{ translate('Home Delivery') }}
                                        @elseif ($order->shipping_type == 'pickup_point')
                                            @if ($order->pickup_point != null)
                                                {{ $order->pickup_point->name }} ({{ translate('Pickip Point') }})
                                            @else
                                                {{ translate('Pickup Point') }}
                                            @endif
                                        @elseif($order->shipping_type == 'carrier')
                                            @if ($order->carrier != null)
                                                {{ $order->carrier->name }} ({{ translate('Carrier') }})
                                                <br>
                                                {{ translate('Transit Time').' - '.$order->carrier->transit_time }}
                                            @else
                                                {{ translate('Carrier') }}
                                            @endif
                                        @endif
                                    </td>
                                    <td class="fw-700">{{ single_price($orderDetail->price) }}</td>
                                    @if (addon_is_activated('refund_request'))
                                        @php
                                            $no_of_max_day = $orderDetail->refund_days;

                                            $last_refund_date = null;
                                            if ($order->delivered_date && $no_of_max_day > 0) {
                                                $last_refund_date = Carbon\Carbon::parse($order->delivered_date)->addDays($no_of_max_day);
                                            }
                                            
                                            $today_date = Carbon\Carbon::now();
                                            
                                        @endphp
                                        <td>
                                            @php
                                                $orderDelivered = $order->delivery_status == 'delivered' || ($order->delivery_status == 'picked_up' && $order->shipping_type == 'pickup_point');
                                            @endphp
                                            @if (
                                                    $orderDetail->product != null &&
                                                    $orderDetail->refund_request == null &&
                                                    $last_refund_date &&
                                                    $today_date <= $last_refund_date &&
                                                    $order->payment_status == 'paid' &&
                                                    $orderDelivered
                                                )

                                                <a href="{{ route('refund_request_send_page', $orderDetail->id) }}"
                                                    class="btn btn-outline-dark btn-sm rounded-0">
                                                    {{ translate('Send') }}
                                                </a>
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 0)
                                                <b class="text-info">{{ translate('Pending') }}</b>
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 2)
                                                <b class="text-danger">{{ translate('Rejected') }}</b>
                                            @elseif ($orderDetail->refund_request != null && $orderDetail->refund_request->refund_status == 1)
                                                <b class="text-success">{{ translate('Approved') }}</b>
                                            @elseif ($orderDetail->product != null && $orderDetail->refund_days != 0)
                                                <b>{{ translate('N/A') }}</b>
                                            @else
                                                <b>{{ translate('Non-refundable') }}</b>
                                            @endif
                                        </td>
                                    @endif
                                        <td class="text-xl-right pr-0">
                                            @php
                                                $canReview = $orderDetail->delivery_status == 'delivered' || ($orderDetail->delivery_status == 'picked_up' && $order->shipping_type == 'pickup_point');
                                            @endphp
                                            @if ($canReview)
                                                <a href="javascript:void(0);" onclick="product_review('{{ $orderDetail->product_id }}', '{{ $order->id }}')"
                                                    class="btn btn-outline-dark btn-sm rounded-0"> {{ translate('Review') }} </a>
                                            @else
                                                <span class="text-danger">{{ translate('Not Delivered Yet') }}</span>
                                            @endif
                                        </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Order Ammount -->
        <div class="col-md-3">
            <div class="card rounded-0 shadow-none border mt-2">
                <div class="card-header border-bottom-0">
                    <b class="fs-16 fw-700 text-dark">{{ translate('Order Ammount') }}</b>
                </div>
                <div class="card-body pb-0">
                    <table class="table-borderless table">
                        <tbody>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Subtotal') }}</td>
                                <td class="text-right">
                                    <span class="strong-600">{{ single_price($order->orderDetails->sum('price')) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Shipping') }}</td>
                                <td class="text-right">
                                    <span class="text-italic">{{ single_price($order->orderDetails->sum('shipping_cost')) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Tax') }}</td>
                                <td class="text-right">
                                    <span class="text-italic">{{ single_price($order->orderDetails->sum('tax')) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Coupon') }}</td>
                                <td class="text-right">
                                    <span class="text-italic">{{ single_price($order->coupon_discount) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-50 fw-600">{{ translate('Total') }}</td>
                                <td class="text-right">
                                    <strong>{{ single_price($order->grand_total) }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($order->payment_status == 'unpaid' && $order->delivery_status == 'pending' && $order->manual_payment == 0)
                <button
                    @if(addon_is_activated('offline_payment'))
                        onclick="select_payment_type({{ $order->id }})"
                    @else
                        onclick="online_payment({{ $order->id }})"
                    @endif
                    class="btn btn-block btn-primary">
                    {{ translate('Make Payment') }}
                </button>
            @endif
        </div>
    </div>

    @if ($order->disputes->count())
        <div class="card rounded-0 shadow-none border mb-4">
            <div class="card-header border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="fs-16 fw-700 text-dark mb-0">{{ translate('Dispute Timeline') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ translate('Reference') }}</th>
                                <th>{{ translate('Reason') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Resolution') }}</th>
                                <th>{{ translate('Updated') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->disputes as $dispute)
                                <tr>
                                    <td>#{{ $dispute->id }}</td>
                                    <td>{{ $dispute->reason ?? translate('Not provided') }}</td>
                                    <td><span class="badge badge-inline badge-secondary text-uppercase">{{ str_replace('_', ' ', $dispute->status) }}</span></td>
                                    <td>{{ $dispute->resolution ? str_replace('_', ' ', $dispute->resolution) : translate('Pending') }}</td>
                                    <td>{{ optional($dispute->updated_at)->format('d M Y, h:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="tradevistaDisputeModal" tabindex="-1" role="dialog" aria-labelledby="tradevistaDisputeLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content rounded-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="tradevistaDisputeLabel">{{ translate('Raise a Dispute') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('tradevista.disputes.store', $order->id) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">{{ translate('Reason') }}</label>
                            <input type="text" name="reason" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ translate('Details') }}</label>
                            <textarea name="details" rows="4" class="form-control" required></textarea>
                            <small class="text-muted">{{ translate('Share specifics so our team can assist faster.') }}</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ translate('Upload evidence (photos, receipts)') }}</label>
                            <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="evidence" class="selected-files">
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ translate('Submit Dispute') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    <!-- Product Review Modal -->
    <div class="modal fade" id="product-review-modal">
        <div class="modal-dialog">
            <div class="modal-content" id="product-review-modal-content">

            </div>
        </div>
    </div>

    <!-- Select Payment Type Modal -->
    <div class="modal fade" id="payment_type_select_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('Select Payment Type') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="order_id" name="order_id" value="{{ $order->id }}">
                    <div class="row">
                        <div class="col-md-2">
                            <label>{{ translate('Payment Type') }}</label>
                        </div>
                        <div class="col-md-10">
                            <div class="mb-3">
                                <select class="form-control aiz-selectpicker rounded-0" onchange="payment_modal(this.value)"
                                    data-minimum-results-for-search="Infinity">
                                    <option value="">{{ translate('Select One') }}</option>
                                    <option value="online">{{ translate('Online payment') }}</option>
                                    <option value="offline">{{ translate('Offline payment') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-right">
                        <button type="button" class="btn btn-sm btn-primary rounded-0 transition-3d-hover mr-1"
                            id="payment_select_type_modal_cancel" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Online payment Modal -->
    <div class="modal fade" id="online_payment_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('Make Payment') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body gry-bg px-3 pt-3" style="overflow-y: inherit;">
                    <form class="" action="{{ route('order.re_payment') }}"
                        method="post">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Payment Method') }}</label>
                            </div>
                            <div class="col-md-10">
                            <div class="mb-3">
                                <select class="form-control selectpicker rounded-0" data-live-search="true" name="payment_option" required>
                                    @include('partials.online_payment_options')
                                    @if (get_setting('wallet_system') == 1 && (auth()->user()->balance >= $order->grand_total))
                                        <option value="wallet">{{ translate('Wallet') }}</option>
                                    @endif
                                </select>
                            </div>
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <button type="button" class="btn btn-sm btn-secondary rounded-0 transition-3d-hover mr-1"
                                data-dismiss="modal">{{ translate('cancel') }}</button>
                            <button type="submit"
                                class="btn btn-sm btn-primary rounded-0 transition-3d-hover mr-1">{{ translate('Confirm') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- offline payment Modal -->
    <div class="modal fade" id="offline_order_re_payment_modal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('Offline Order Payment') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="offline_order_re_payment_modal_body"></div>
            </div>
        </div>
    </div>

@endsection


@section('script')
    <script type="text/javascript">

        function product_review(product_id,order_id) {
            $.post('{{ route('product_review_modal') }}', {
                _token: '{{ @csrf_token() }}',
                product_id: product_id,
                order_id: order_id
            }, function(data) {
                $('#product-review-modal-content').html(data);
                $('#product-review-modal').modal('show', {
                    backdrop: 'static'
                });
                AIZ.extra.inputRating();
            });
        }

        function select_payment_type(id) {
            $('#payment_type_select_modal').modal('show');
        }

        function payment_modal(type) {
            if (type == 'online') {
                $("#payment_select_type_modal_cancel").click();
                online_payment();
            } else if (type == 'offline') {
                $("#payment_select_type_modal_cancel").click();
                $.post('{{ route('offline_order_re_payment_modal') }}', {
                    _token: '{{ csrf_token() }}',
                    order_id: '{{ $order->id }}'
                }, function(data) {
                    $('#offline_order_re_payment_modal_body').html(data);
                    $('#offline_order_re_payment_modal').modal('show');
                });
            }
        }

        function online_payment() {
            $('input[name=customer_package_id]').val();
            $('#online_payment_modal').modal('show');
        }

    </script>
@endsection
