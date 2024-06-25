<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSocial;
use App\Traits\APIHandleClass;
use App\Traits\AuthHandleTrait;
use App\Mail\SendResetPassword;
use App\Mail\SendForgetPassword;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Traits\SendMailTrait;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    use APIHandleClass,AuthHandleTrait,SendMailTrait;

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
            if (!Hash::check('social_login', $user->password)) {
                $this->setStatusMessage(false);
                $this->setMessage('this user is a yelogift user , you should login using password');
                return $this->returnResponse();
            }
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
            $user->password = Hash::make('social_login');
            $user->save();

            $socialCreate = new UserSocial();
            $socialCreate->user_id = $user->id;
            $socialCreate->provider = $request->provider;
            $socialCreate->provider_id = $request->client_id;
            $socialCreate->save();
        }
        $credentials=['email'=>$user->email,'password'=>'social_login'];
        if($token = Auth::guard('web')->attempt($credentials)){
        $this->setData(['token' => $token, 'user' => $user, 'role' => 'user', 'auth'=>'social']);
        $this->setMessage(__('translate.login_success_message'));
        return $this->returnResponse();
    }else{
        $this->setStatusMessage(false);
        $this->setMessage(__('translate.login_failed_message'));
        return $this->returnResponse();
    }
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
                $this->setStatusCode(200);
                $this->setMessage('password reset successfully');
            }else{
                $this->setStatusCode(422);
                $this->setMessage('Old password is incorrect');
            }
            // Return a success message.

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

    function forgetPassword(Request $request){
        try{
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email', // The user's email is required
            ]);

            // If the validation fails, return the errors
            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            $user = User::where('email',$request->email)->first();

            if ($user){
                $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()_+';
                $new_password = substr(str_shuffle($charset), 0, 12);
                $user->password = Hash::make($new_password);
                $user->save();

                //send email with nwe password
                $content_email = "Your New Password is ".$new_password;
                $this->send_mail($user->email, $user->name, "YELOGIFT Forget Password", $content_email);
                $this->setStatusCode(200);
                $this->setMessage('password reset successfully');
            }else{
                $this->setStatusCode(422);
                $this->setMessage('Sorry, an error occurred, please try again');
            }
            // Return a success message.

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

