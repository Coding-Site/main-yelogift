<?php

namespace App\Http\Controllers\User;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use App\Http\Controllers\Controller;
use App\Mail\SendResetPassword;
use App\Models\User;
use App\Models\UserSocial;
use App\Traits\APIHandleClass;
use App\Traits\AuthHandleTrait;
use Exception;
use Google\Service\Docs\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWAuth\Facades\JWAuth;

class AuthController extends Controller
{
    use APIHandleClass,AuthHandleTrait;
   
    public function logout(Request $request)
    {
        // $token = JWAuth::getToken();
        // JWAuth::invalidate($token);
        Auth::guard('web')->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);
    }
    // public function logout()
    // {
    //     Auth::guard('web')->logout();

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Logged out successfully'
    //     ], 200);
    // }

    function register(Request $request)
    {
        // Validate the request data
        try {
            // Define the validation rules and custom messages
            $validator=Validator::make($request->all(),[
                'name'=>'required',                 // The user's name is required
                'phone'=>'required|unique:users|min:10|max:12', // The user's phone is required, unique and has a valid length
                'email'=> 'required|email|unique:users', // The user's email is required, valid and unique
                'password'=>'required|min:6' // The user's password is required, has a valid length and matches the specified format
            ]);

            // If the validation fails, return the errors
            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            // Create a new user and save it to the database
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->save();

            // Return a success message
            $this->setMessage(__('translate.register_success'));

            return $this->returnResponse();

        } catch (Exception $e) {
            // Return an error message in case of server error
            $this->setStatusCode(500);
            $this->setMessage(__('translate.error_server'));
            $this->setStatusMessage(false);

            return $this->returnResponse();
        }
    }
  

    public function socialLogin(Request $request)
    {
        $user = User::where('email',$request->email)->first();
        if ($user) {
            // Check if there is a social login record for the user and the specified provider.
            $social = UserSocial::where('user_id', $user->id)
                ->where('provider', $request->provider)
                ->where('provider_id', $request->client_id)
                ->first();

            // If there is no social login record, create a new one.
            if (!$social) {
                $socialCreate = new UserSocial();
                $socialCreate->user_id = $user->id;
                $socialCreate->provider = $request->provider;
                $socialCreate->provider_id = $request->client_id;
                $socialCreate->save();
            }
        }
        // If the user does not exist in our server, create a new user.
        else {
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = null;
            $user->password = Hash::make($request->client_id);
            $user->save();

            $socialCreate = new UserSocial();
            $socialCreate->user_id = $user->id;
            $socialCreate->provider = $request->provider;
            $socialCreate->provider_id = $request->client_id;
            $socialCreate->save();
        }
        $credentials=['email'=>$user->email,'password'=>$request->client_id];
        $token = Auth::guard('web')->attempt($credentials);
        $this->setData(['token' => $token, 'user' => $user]);
        $this->setMessage(__('translate.login_success_message'));
        return $this->returnResponse();
    }

    public function update(Request $request)
    {

    $user = User::findOrFail(auth()->user()->id);
    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone = $request->phone;

    $user->save();

    return response()->json([
        'message' => 'User updated successfully',
        'user' => $user
    ]);
    }

    public function index()
    {

    $user = auth()->user();
    return response()->json([
        'user' => $user
    ]);
    }
 
    function resetpassword(Request $request){
        try{
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'new_password' => 'required', // The user's email is optional, valid and exists in the users table.
                'old_password'=>'required', // The user's phone is optional and exists in the users table.
            ]);

            // If the validation fails, return the errors
            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            $user = User::where('id',auth()->user()->id)->first();
            if (Hash::check($request->old_password, $user->password)){
                $user->password = Hash::make($request->new_password);
                $user->save();
            }else{
                $this->setStatusCode(422);
                $this->setMessage('Old password is incorrect');
            }
            // Return a success message.
            $this->setStatusCode(200);
            $this->setMessage('password reset successfully');
            return $this->returnResponse();
        }
        // Return an error message in case of server error.
        catch(Exception $e){
            $this->setStatusCode(500);
            $this->setMessage(__('translate.error_server'));
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

    }

    