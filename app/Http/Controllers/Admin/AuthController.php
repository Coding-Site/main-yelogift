<?php

namespace App\Http\Controllers\Admin;

use App\Traits\APIHandleClass;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use APIHandleClass;
    public function login(Request $request)
    {

        $credentials = $request->only('email', 'password');
        if ($token = Auth::guard('admin')->attempt($credentials)) {
            $admin =Admin::find(Auth::guard('admin')->user()->getAuthIdentifier());
            $tokenData = [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('admin')->factory()->getTTL() * 60
            ];
            $this->setData(['token'=> $tokenData,'admin'=> $admin]);
            $this->setMessage(__('translate.login_success_message'));
            return $this->returnResponse();
        }

        $this->setMessage(__('translate.error_login'));
        $this->setStatusCode(401);
        $this->setStatusMessage(false);
        return $this->returnResponse();
    }

}
