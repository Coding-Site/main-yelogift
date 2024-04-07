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
     */
    public function index()
    {
        $setting = Setting::get();
        $this->setData($setting);
        return $this->returnResponse();

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
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

            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
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

            $this->setMessage(__('translate.setting_update_success'));
            return $this->returnResponse();
        }catch (\Exception $e){
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
