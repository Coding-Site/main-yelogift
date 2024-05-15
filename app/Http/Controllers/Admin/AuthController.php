<?php

namespace App\Http\Controllers\Admin;

use App\Traits\APIHandleClass;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\AuthHandleTrait;
use Exception;

class AuthController extends Controller
{
    use APIHandleClass,AuthHandleTrait;
    /**
     * Authenticate an admin and generate a token for them
     *
     * @param Request $request The HTTP request object containing the admin's email and password
     * @return \Illuminate\Http\JsonResponse The JSON response containing the token and admin data
     */
    public function login(Request $request)
    {
        
        try {
            // Extract the admin's email and password from the request
        // $credentials = $request->only('login', 'password');
        $credentials = $this->type_credential($request->login,$request->password);
        // Attempt to authenticate the admin
        if ($token = Auth::guard('admin')->attempt($credentials)) {

            // Find the admin in the database
            $user =Admin::find(Auth::guard('admin')->user()->getAuthIdentifier());
            $role='admin';
        } elseif($token = Auth::guard('web')->attempt($credentials)){
            $user = User::find(Auth::guard('web')->user()->getAuthIdentifier());
            $role='user';
        }else{
            $this->setMessage(__('translate.error_login'));
            $this->setStatusCode(401);
            $this->setStatusMessage(false);
    
            // Return the JSON response
            return $this->returnResponse();
        }
                // Set the response data
                $tokenData = [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('web')->factory()->getTTL() * 60
                ];
                $this->setData(['token' => $tokenData, 'user' => $user, 'role'=>$role]);
                $this->setMessage(__('translate.login_success_message'));

                // Return the response
                return $this->returnResponse();
        

        // Set the error response
       
        } catch (Exception $e) {
            // Error with the server
            $this->setStatusCode(500);
            $this->setMessage(__('translate.error_server'));
            $this->setData([
                'error'=>$e->getMessage()
            ]);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

}
