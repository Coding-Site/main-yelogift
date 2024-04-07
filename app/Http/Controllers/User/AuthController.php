<?php

namespace App\Http\Controllers\User;

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

class AuthController extends Controller
{
    use APIHandleClass,AuthHandleTrait;
    /**
     * Authenticates a user and generates a token for them
     *
     * @param Request $request The HTTP request object
     * @throws Exception When there's an error with the server
     */
    function login(Request $request)
    {
        try {

            // Attempt to authenticate the user
            $credentials = $this->type_credential($request->login,$request->password);
            if ($token = Auth::guard('web')->attempt($credentials)) {
                $user =User::find(Auth::guard('web')->user()->getAuthIdentifier());
                $tokenData = [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('web')->factory()->getTTL() * 60
                ];

                // Set the response data
                $this->setData(['token' => $tokenData, 'user' => $user]);
                $this->setMessage(__('translate.login_success_message'));
                return $this->returnResponse();
            }
            // User authentication failed
            $this->setMessage(__('translate.error_login'));
            $this->setStatusCode(401);
            $this->setStatusMessage(false);
            return $this->returnResponse();


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
    /**
     * Registers a new user
     *
     * @param Request $request The HTTP request object containing the user's data
     * @return \Illuminate\Http\JsonResponse The JSON response with the result of the registration
     * @throws Exception When there's an error with the server
     */
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
    function resetpassword(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'nullable|email|exists:users,email',
                'phone'=>'nullable|exists:users,phone',

            ]);

            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            $password = Str::random(11);
            if(isset($request->email)){
                $user = User::where('email', $request->email)->first();
                $user->password = Hash::make($password);
                $user->save();

                Mail::to($request->email)->send(new SendResetPassword($user->name, $password));
            }elseif(isset($request->phone)){
                $user = User::where('phone', $request->phone)->first();
                $user->password = Hash::make($password);
                $user->save();
            }else{
                $this->setMessage('email or phone is required');
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            $this->setMessage('password reset successfully');
            return $this->returnResponse();
        }catch(Exception $e){
            $this->setStatusCode(500);
            $this->setMessage(__('translate.error_server'));
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }


    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['facebook', 'google'])) {
            return response()->json(['error' => 'Please login using facebook, or google'], 422);
        }
    }

     /**
     * Redirect the user to the Provider authentication page.
     *
     * @param $provider
     * @return JsonResponse
     */
    public function redirectToProvider($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        return Socialite::driver($provider)->redirect();
    }


    /**
     * Social Login
     */
    public function socialLogin(Request $request)
    {
        $provider = $request->input('provider_name');
        $token = $request->input('access_token');
        // get the provider's user. (In the provider server)
        $providerUser = Socialite::driver($provider)->user();
        // check if access token exists etc..
        // search for a user in our server with the specified provider id and provider name
        if($providerUser){
            $user = User::where('email',$providerUser->getEmail())->get();
            if($user){
                $social = UserSocial::where('user_id',$user->id)->where('provider',$provider)->where('provider_id',$providerUser->getId())->get();
                if(!$social){
                    $socialCreate = new UserSocial();
                    $socialCreate->user_id = $user->id;
                    $socialCreate->provider = $provider;
                    $socialCreate->provider_id = $providerUser->getId();
                    $socialCreate->save();
                }
            }else{
                $user = new User;
                $user->name = $providerUser->getName();
                $user->email = $providerUser->getEmail();
                $user->photo = $providerUser->getAvatar();
                $user->save();

                $socialCreate = new UserSocial();
                $socialCreate->user_id = $user->id;
                $socialCreate->provider = $provider;
                $socialCreate->provider_id = $providerUser->getId();
                $socialCreate->save();
            }
            // create a token for the user, so they can login
            $token = $user->createToken('social-login-web')->accessToken;
            // return the token for usage
            $this->setData(['token' => $token, 'user' => $user]);
            $this->setMessage(__('translate.login_success_message'));
            return $this->returnResponse();
        }
        $this->setMessage(__('translate.error_social_login'));
        $this->setStatusCode(401);
        $this->setStatusMessage(false);
        return $this->returnResponse();

    }
}
