<?php

namespace App\Http\Controllers\Admin;

use App\Traits\APIHandleClass;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\UserSocial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\AuthHandleTrait;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Google\Service\Docs\Response;

class AuthController extends Controller
{
    use APIHandleClass,AuthHandleTrait;
    /**
     * Authenticate an admin and generate a token for them
     *
     * @param Request $request The HTTP request object containing the admin's email and password
     * @return \Illuminate\Http\JsonResponse The JSON response containing the token and admin data
     */
    public function logout()
    {
        // $token = JWTAuth::parseToken();
        // $token->revoke();
        Auth::guard('admin')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);
    }
    public function editAdmin(Request $request ,$id)
    {
        $validator=Validator::make($request->all(),[
            'name'=>'required',                
            'email'=> 'required|email', 
            'password'=>'required' 
        ]);
        if ($validator->fails()) {
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $admin = Admin::findOrFail($id);
        if(Hash::check($request->password,$admin->password)){
            $admin->email = $request->email;
            $admin->name = $request->name;
            $admin->save();
        }else{
            return response()->json([
                'status' => 'failed',
                'message' => 'password is not correct'
            ], 401);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'data changed successfully'
        ], 200);
    }
    public function resetPassword(Request $request ,$id)
    {
        $validator=Validator::make($request->all(),[
            'new_password'=>'required|min:6',
            'confirm_password'=>'required|min:6',
            'password'=>'required' 
        ]);
        if ($request->new_password != $request->confirm_password){
            $this->setMessage('new password not match');
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        };
        if ($validator->fails()) {
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $admin = Admin::findOrFail($id);
        if(Hash::check($request->password,$admin->password)){
            $admin->password = Hash::make($request->new_password);
            $admin->save();
        }else{
            return response()->json([
                'status' => 'failed',
                'message' => 'password is not correct'
            ], 401);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'password changed successfully'
        ], 200);
    }
    
    public function login(Request $request)
    {
        
        try {
            // Extract the admin's email and password from the request
        // $credentials = $request->only('login', 'password');
        $user = User::where('email',$request->login)->first();
        if(!$user){$user = User::where('phone',$request->login)->first();}
        if($user){
            $userSocial = UserSocial::where('user_id',$user->id)->first();
            if($userSocial){
                $this->setMessage('invalid login for social user , please login using '.$userSocial->provider);
                $this->setStatusCode(401);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
        }
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
                $this->setData(['token' => $tokenData, 'user' => $user, 'role'=>$role, 'auth'=>'yelogift']);
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
