<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\PaymentSetting;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    use APIHandleClass;

    /**
     * Display a listing of the resource.
     *
     * This function retrieves all the currencies from the database and sets
     * the data for the response. Then it returns the response.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Retrieve all the currencies from the database
        $currencies = Currency::all();

        // Set the data for the response
        $this->setData($currencies);

        // Return the response
        return $this->returnResponse();
    }

    /**
     * Store a newly created resource in storage.
     *
     * This function validates the request data, creates a new Currency object
     * and saves it to the database. If the validation fails, it returns an
     * error response. If an exception occurs during the process, it returns
     * an error response. Otherwise, it returns a success response with the
     * created Currency object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'symbol' => 'required',
                'image'=>'required|image',
                'charge_rate' => 'nullable|numeric',
                'charge_percent' => 'nullable|numeric',
            ]);

            // If the validation fails, return an error response
            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            // Create a new Currency object and set its properties
            $currency = new Currency();
            $currency->name = $request->name;
            $currency->symbol = $request->symbol;
            $currency->icon = $request->image->store('currency_icon', 'public');
            $currency->charge_rate = $request->has('charge_rate') ? $request->charge_rate : 0;
            $currency->charge_percent = $request->has('charge_percent') ? $request->charge_percent : 0;

            // Save the Currency object to the database
            $currency->save();

            // Return a success response with the created Currency object
            $this->setMessage(__('translate.currency_store_success'));
            $this->setData($currency);
            return $this->returnResponse();
        } catch (\Exception $e) {
            // Return an error response if an exception occurs
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
     * Retrieves a Currency object from the database and returns it as a response.
     *
     * @param  \App\Models\Currency  $currency The Currency object to be displayed.
     * @return \Illuminate\Http\Response The HTTP response containing the Currency object.
     */
    public function show(Currency $currency)
    {
        // Set the data to the specified Currency object.
        $this->setData($currency);

        // Return the HTTP response with the Currency object.
        return $this->returnResponse();
    }

    /**
     * Update the specified resource in storage.
     *
     * This function updates a Currency object in the database. It validates
     * the request data and updates the Currency object with the new data.
     * If the validation fails, it returns an error response. If an exception
     * occurs during the process, it returns an error response. Otherwise, it
     * returns a success response.
     *
     * @param  \Illuminate\Http\Request  $request The request object containing the updated data.
     * @return \Illuminate\Http\Response The HTTP response.
     */
    public function update(Request $request)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'currancy_id'=>'required|exists:currencies,id', // The currency ID must exist in the currencies table.
                'name' => 'required', // The name field is required.
                'symbol' => 'required', // The symbol field is required.
                'image'=>'required|image',
                'charge_rate' => 'nullable|numeric', // The charge_rate field can be null or a numeric value.
                'charge_percent' => 'nullable|numeric', // The charge_percent field can be null or a numeric value.
            ]);

            // If the validation fails, return an error response
            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            // Find the Currency object and update its properties
            $currency = Currency::find($request->currancy_id);
            $currency->name = $request->name;
            $currency->symbol = $request->symbol;
            $currency->charge_rate = $request->has('charge_rate') ? $request->charge_rate : 0;
            $currency->charge_percent = $request->has('charge_percent') ? $request->charge_percent : 0;
            if($request->hasFile('image')){
                $currency->icon = $request->icon->store('currency_icon', 'public');
            }
            // Save the Currency object to the database
            $currency->save();

            // Return a success response
            $this->setMessage(__('translate.currency_update_success'));
            return $this->returnResponse();
        } catch (\Exception $e) {
            // Return an error response if an exception occurs
            $this->setMessage(__('translate.error_server'));
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * This function deletes a Currency object from the database.
     * It first tries to find the Currency object based on the provided
     * currency_id. If the object is not found, it returns an error response.
     * If the object is found, it deletes the object and returns a success response.
     * If an exception occurs during the process, it returns an error response.
     *
     * @param  int  $currency_id The ID of the Currency object to be deleted.
     * @return \Illuminate\Http\Response The HTTP response.
     */
    public function destroy($currency_id)
    {
        try {
            // Find the Currency object
            $currency = Currency::find($currency_id);
            PaymentSetting::where('currency_id',$currency_id)->delete();
            // If the Currency object is not found, return an error response
            if (!$currency) {
                $this->setMessage(__('translate.currency_not_found'));
                $this->setStatusCode(404);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            // Delete the Currency object from the database
            $currency->delete();

            // Return a success response
            $this->setMessage(__('translate.currency_delete_success'));
            return $this->returnResponse();

        } catch (\Exception $e) {
            // Return an error response if an exception occurs
            $this->setMessage(__('translate.error_server'));
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

}
