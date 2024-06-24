<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Traits\APIHandleClass;
use Google\Service\Slides\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentSettingController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentSetting = PaymentSetting::with('currency')->get();
        $this->setData($paymentSetting);
        return $this->returnResponse();
    }

    public function show($id)
    {
        $paymentSetting = PaymentSetting::find($id);
        $this->setData($paymentSetting);
        return $this->returnResponse();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'address'=>'required',
            'currency_id'=>'required|exists:currencies,id',
            'blockchain_type'=>'required',
            'payment_qr'=>'required|image'
        ]);
        
        if($validator->fails()){
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $paymentSetting = new PaymentSetting();
        $paymentSetting->address = $request->address;
        $paymentSetting->currency_id = $request->currency_id;
        $paymentSetting->blockchain_type = $request->blockchain_type;
        $paymentSetting->payment_qr = $request->payment_qr->store('payment_qr', 'public');
        $paymentSetting->save();
        $this->setData($paymentSetting);
        $this->setMessage(__('translate.create_payment_setting_success'));
        return $this->returnResponse();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'payment_id'=>'required|exists:payment_settings,id',
            'address'=>'required',
            'currency_id'=>'required|exists:currencies,id',
            'blockchain_type'=>'required',
            'payment_qr'=>'required|image'
        ]);

        if($validator->failed()){
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        $paymentSetting = PaymentSetting::find($request->payment_id);
        $paymentSetting->address = $request->address;
        $paymentSetting->currency_id = $request->currency_id;
        $paymentSetting->blockchain_type = $request->blockchain_type;
        
        if($request->hasFile('payment_qr')){
            $paymentSetting->payment_qr = $request->payment_qr->store('payment_qr', 'public');
        }
        $paymentSetting->save();
        $this->setData($paymentSetting);
        $this->setMessage(__('translate.update_payment_setting_success'));
        return $this->returnResponse();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $paymentSetting_id)
    {
        $paymentSetting = PaymentSetting::find($paymentSetting_id);
        if(!$paymentSetting){
            $this->setMessage(__('translate.payment_setting_not_found'));
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $paymentSetting->delete();
        $this->setMessage(__('translate.delete_payment_setting_success'));
        return $this->returnResponse();
    }
}
