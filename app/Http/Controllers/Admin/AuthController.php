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
    /**
     * Authenticate an admin and generate a token for them
     *
     * @param Request $request The HTTP request object containing the admin's email and password
     * @return \Illuminate\Http\JsonResponse The JSON response containing the token and admin data
     */
    public function login(Request $request)
    {
        // Extract the admin's email and password from the request
        $credentials = $request->only('email', 'password');

        // Attempt to authenticate the admin
        if ($token = Auth::guard('admin')->attempt($credentials)) {

            // Find the admin in the database
            $admin =Admin::find(Auth::guard('admin')->user()->getAuthIdentifier());

            // Prepare the token data
            $tokenData = [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('admin')->factory()->getTTL() * 60
            ];

            // Set the response data
            $this->setData(['token'=> $tokenData,'admin'=> $admin]);
            $this->setMessage(__('translate.login_success_message'));

            // Return the JSON response
            return $this->returnResponse();
        }

        // Set the error response
        $this->setMessage(__('translate.error_login'));
        $this->setStatusCode(401);
        $this->setStatusMessage(false);

        // Return the JSON response
        return $this->returnResponse();
    }

}
