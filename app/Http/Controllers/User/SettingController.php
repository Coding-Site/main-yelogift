<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Models\Setting;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use APIHandleClass;
    public function get_all_setting(){

        $this->setData([
            'site_name'=>Setting::where('key','site-name')->first()->value,
            'logo'=>Setting::where('key','logo')->first()->value,
            'favicon'=>Setting::where('key','favicon')->first()->value,
            'main_light_color'=>Setting::where('key','main-light-color')->first()->value,
            'main_dark_color'=>Setting::where('key','main-dark-color')->first()->value,
            'primary_light_color'=>Setting::where('key','primary-light-color')->first()->value,
            'primary_dark_color'=>Setting::where('key','primary-dark-color')->first()->value,
            'footer_text'=>Setting::where('key','footer-text')->first()->value,
        ]);
        return $this->returnResponse();
    }
    public function get_payment_setting(){
        $paymentSeting = PaymentSetting::get();
        $this->setData($paymentSeting);
        return $this->returnResponse();
    }

}
