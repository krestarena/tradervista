<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\WalletController;
use App\Models\CombinedOrder;
use App\Models\Order;
use Session;
use Auth;
use Exception;
use Rave as Flutterwave;

class FlutterwaveController extends Controller
{
    public function pay()
    {
        if(Session::has('payment_type')){
            $paymentType = Session::get('payment_type');
            $paymentData = Session::get('payment_data');

            if($paymentType == 'cart_payment'){
                $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                return $this->initialize($combined_order->grand_total);
            }
            elseif($paymentType == 'order_re_payment'){
                $order = Order::findOrFail($paymentData['order_id']);
                return $this->initialize($order->grand_total);
            }
            elseif ($paymentType == 'wallet_payment') {
                return $this->initialize($paymentData['amount']);
            }
            elseif ($paymentType == 'customer_package_payment') {
                $customer_package_id = $paymentData['customer_package_id'];
                $package_details = CustomerPackage::findOrFail($customer_package_id);
                return $this->initialize($package_details->amount);
            }
            elseif ($paymentType == 'seller_package_payment') {
                $seller_package_id = $paymentData['seller_package_id'];
                $package_details = SellerPackage::findOrFail($seller_package_id);
                return $this->initialize($package_details->amount);
            }
        }
    }

    public function initialize($amount)
    {
        //This generates a payment reference
        $reference = Flutterwave::generateReference();

        // Enter the details of the payment
        $data = [
            'payment_options' => 'card,banktransfer',
            'amount' => $amount,
            'email' => Auth::user()->email,
            'tx_ref' => $reference,
            'currency' => env('FLW_PAYMENT_CURRENCY_CODE'),
            'redirect_url' => route('flutterwave.callback'),
            'customer' => [
                'email' => Auth::user()->email,
                "phone_number" => Auth::user()->phone,
                "name" => Auth::user()->name
            ],

            "customizations" => [
                "title" => 'Payment',
                "description" => ""
            ]
        ];

        $payment = Flutterwave::initializePayment($data);


        if ($payment['status'] !== 'success') {
            // notify something went wrong
            return;
        }

        return redirect($payment['data']['link']);
    }

    /**
     * Obtain Rave callback information
     * @return void
     */
    public function callback()
    {
        $status = request()->status;

        //if payment is successful
        if ($status ==  'successful') {
            $transactionID = Flutterwave::getTransactionIDFromCallback();
            $data = Flutterwave::verifyTransaction($transactionID);

            try{
                $payment = $data['data'];
                $payment_type = Session::get('payment_type');
                $paymentData = session()->get('payment_data');

                if($payment['status'] == "successful"){
                    if ($payment_type == 'cart_payment') {
                        return (new CheckoutController)->checkout_done(session()->get('combined_order_id'), json_encode($payment));
                    } elseif ($payment_type == 'order_re_payment') {
                        return (new CheckoutController)->orderRePaymentDone($paymentData, json_encode($payment));
                    } elseif ($payment_type == 'wallet_payment') {
                        return (new WalletController)->wallet_payment_done($paymentData, json_encode($payment));
                    } elseif ($payment_type == 'customer_package_payment') {
                        return (new CustomerPackageController)->purchase_payment_done($paymentData, json_encode($payment));
                    } elseif ($payment_type == 'seller_package_payment') {
                        return (new SellerPackageController)->purchase_payment_done($paymentData, json_encode($payment));
                    }
                }
            }
            catch(Exception $e){
                //dd($e);
            }
        }
        elseif ($status ==  'cancelled'){
            //Put desired action/code after transaction has been cancelled here
            flash(translate('Payment cancelled'))->error();
            return redirect()->route('home');
        }
        //Put desired action/code after transaction has failed here
        flash(translate('Payment failed'))->error();
        return redirect()->route('home');
    }
}
