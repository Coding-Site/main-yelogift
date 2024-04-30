<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\PaymentHandleTrait;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use PaymentHandleTrait;
    function checkout(Request $request){

        return $this->initiateBinancePay(1212,"test","test",25.35);
    }
}
