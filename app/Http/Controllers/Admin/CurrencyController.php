<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    use APIHandleClass;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currencies = Currency::all();
        $this->setData($currencies);
        return $this->returnResponse();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'symbol' => 'required',
                'charge_rate' => 'nullable|numeric',
                'charge_percent' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            $currency = new Currency();
            $currency->name = $request->name;
            $currency->symbol = $request->symbol;
            if($request->has('charge_rate')){
                $currency->charge_rate = $request->charge_rate;
            }else{
                $currency->charge_rate = 0;
            }
            if($request->has('charge_percent')){
                $currency->charge_percent = $request->charge_percent;
            }else{
                $currency->charge_percent = 0;
            }
            $currency->save();

            $this->setMessage(__('translate.currency_store_success'));
            $this->setData($currency);
            return $this->returnResponse();
        } catch (\Exception $e) {
            $this->setMessage(__('translate.error_server'));
            $this->setData([
                'error'=>$e->getMessage()
            ]);
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function show(Currency $currency)
    {
        $this->setData($currency);
        return $this->returnResponse();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'currancy_id'=>'required|exists:currencies,id',
                'name' => 'required',
                'symbol' => 'required',
                'charge_rate' => 'nullable|numeric',
                'charge_percent' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            $currency = Currency::find($request->currancy_id);
            $currency->name = $request->name;
            $currency->symbol = $request->symbol;
            if($request->has('charge_rate')){
                $currency->charge_rate = $request->charge_rate;
            }else{
                $currency->charge_rate = 0;
            }
            if($request->has('charge_percent')){
                $currency->charge_percent = $request->charge_percent;
            }else{
                $currency->charge_percent = 0;
            }
            $currency->save();

            $this->setMessage(__('translate.currency_update_success'));
            return $this->returnResponse();
        } catch (\Exception $e) {
            $this->setMessage(__('translate.error_server'));
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function destroy($currency_id)
    {
        try {
            $currency = Currency::find($currency_id);
            if (!$currency) {
                $this->setMessage(__('translate.currency_not_found'));
                $this->setStatusCode(404);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            $currency->delete();
            $this->setMessage(__('translate.currency_delete_success'));
            return $this->returnResponse();
        } catch (\Exception $e) {
            $this->setMessage(__('translate.error_server'));
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

}
