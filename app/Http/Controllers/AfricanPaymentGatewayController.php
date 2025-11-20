<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class AfricanPaymentGatewayController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:african_pg_configuration'])->only('configuration');
        $this->middleware(['permission:african_pg_credentials_configuration'])->only('credentials_index');
    }

    public function credentials_index()
    {
        $payment_methods = PaymentMethod::where('addon_identifier', 'african_pg')->get();
        return view('african_pg.configurations.index', compact('payment_methods'));
    }
}
