<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     *
     * This function retrieves all the settings from the database and sets the
     * retrieved data as the response payload. It then returns the response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Retrieve all the settings from the database
        $setting = Setting::get();

        // Set the retrieved settings as the response payload
        $this->setData($setting);

        // Return the response
        return $this->returnResponse();
    }

    /**
     * Update the specified resource in storage.
     *
     * This function updates the settings in the database with the values
     * provided in the request. It uses Laravel's validation to ensure
     * that the required fields are present and valid. If the validation
     * fails, it returns a JSON response with the error message. If the
     * validation passes, it updates the settings in the database using
     * the `updateOrCreate` method of the `Setting` model. Finally, it
     * returns a JSON response with a success message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'site_name' => 'required',
                'logo_dark' => 'nullable|image',
                'logo_light'=> 'nullable|image',
                'favicon'=>'nullable|image',
                'main_light_color'=>'required',
                'main_dark_color'=>'required',
                'primary_light_color'=>'required',
                'primary_dark_color'=>'required',
                'footer_text'=>'required',
                'email_enable'=>'required',
                'insite_enable'=>'required',
                'manual_enable'=>'required',
            ]);

            // If validation fails, return error response
            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            // Update or create settings in the database
            Setting::updateOrCreate(
                ['key' => 'site-name'],
                ['value' => $request->site_name]
            );

            if($request->hasFile('logo_dark')){
                Setting::updateOrCreate(
                    ['key' => 'logo-dark'],
                    ['value' => $request->logo_dark]
                );
            }

            if($request->hasFile('logo_light')){
                Setting::updateOrCreate(
                    ['key' => 'logo-light'],
                    ['value' => $request->logo_light]
                );
            }

            if($request->hasFile('favicon')){
                Setting::updateOrCreate(
                    ['key' => 'favicon'],
                    ['value' => $request->favicon]
                );
            }

            Setting::updateOrCreate(
                ['key' => 'main-light-color'],
                ['value' => $request->main_light_color]
            );

            Setting::updateOrCreate(
                ['key' => 'main-dark-color'],
                ['value' => $request->main_dark_color]
            );

            Setting::updateOrCreate(
                ['key' => 'primary-light-color'],
                ['value' => $request->primary_light_color]
            );

            Setting::updateOrCreate(
                ['key' => 'primary-dark-color'],
                ['value' => $request->primary_dark_color]
            );

            Setting::updateOrCreate(
                ['key' => 'footer-text'],
                ['value' => $request->footer_text]
            );

            Setting::updateOrCreate(
                ['key' => 'email-enable'],
                ['value' => $request->email_enable]
            );

            Setting::updateOrCreate(
                ['key' => 'insite-enable'],
                ['value' => $request->insite_enable]
            );

            Setting::updateOrCreate(
                ['key' => 'manual-enable'],
                ['value' => $request->manual_enable]
            );

            // Return success response
            $this->setMessage(__('translate.setting_update_success'));
            return $this->returnResponse();
        } catch (\Exception $e) {
            // If an exception occurs, return error response
            $this->setMessage(__('translate.error_server'));
            $this->setData([
                'error'=>$e->getMessage()
            ]);
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }
}
