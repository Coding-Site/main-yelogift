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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Google_Client;
use Google_Service_Oauth2;
class AuthController extends Controller
{
    use APIHandleClass,AuthHandleTrait;
   
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
 
    // function resetpassword(Request $request){
    //     try{
    //         // Validate the request data
    //         $validator = Validator::make($request->all(), [
    //             'email' => 'nullable|email|exists:users,email', // The user's email is optional, valid and exists in the users table.
    //             'phone'=>'nullable|exists:users,phone', // The user's phone is optional and exists in the users table.
    //         ]);

    //         // If the validation fails, return the errors
    //         if ($validator->fails()) {
    //             $this->setMessage($validator->errors()->first());
    //             $this->setStatusCode(400);
    //             $this->setStatusMessage(false);
    //             return $this->returnResponse();
    //         }

    //         // Generate a new random password.
    //         $password = Str::random(11);

    //         // Check if the request contains an email.
    //         if(isset($request->email)){
    //             // Find the user with the provided email.
    //             $user = User::where('email', $request->email)->first();
    //             // Update the user's password.
    //             $user->password = Hash::make($password);
    //             $user->save();

    //             // Send a new password to the user's email.
    //             Mail::to($request->email)->send(new SendResetPassword($user->name, $password));
    //         }
    //         // Check if the request contains a phone.
    //         elseif(isset($request->phone)){
    //             // Find the user with the provided phone.
    //             $user = User::where('phone', $request->phone)->first();
    //             // Update the user's password.
    //             $user->password = Hash::make($password);
    //             $user->save();
    //         }
    //         // If neither email nor phone is provided.
    //         else{
    //             $this->setMessage('email or phone is required');
    //             $this->setStatusCode(400);
    //             $this->setStatusMessage(false);
    //             return $this->returnResponse();
    //         }

    //         // Return a success message.
    //         $this->setMessage('password reset successfully');
    //         return $this->returnResponse();
    //     }
    //     // Return an error message in case of server error.
    //     catch(Exception $e){
    //         $this->setStatusCode(500);
    //         $this->setMessage(__('translate.error_server'));
    //         $this->setStatusMessage(false);
    //         return $this->returnResponse();
    //     }
    // }

    }

    