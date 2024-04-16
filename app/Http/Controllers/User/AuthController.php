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
     * @param Request $request The HTTP request object containing the user's login credentials
     * @return \Illuminate\Http\JsonResponse The JSON response with the generated token and user data
     * @throws Exception When there's an error with the server
     */
    function login(Request $request)
    {
        try {
            // Attempt to authenticate the user

            // Type the user's login credentials based on the request data
            $credentials = $this->type_credential($request->login,$request->password);

            // Attempt to authenticate the user with the provided credentials
            if ($token = Auth::guard('web')->attempt($credentials)) {

                // Find the authenticated user
                $user = User::find(Auth::guard('web')->user()->getAuthIdentifier());

                // Generate the token data
                $tokenData = [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('web')->factory()->getTTL() * 60
                ];

                // Set the response data
                $this->setData(['token' => $tokenData, 'user' => $user]);
                $this->setMessage(__('translate.login_success_message'));

                // Return the response
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
     * Registers a new user.
     *
     * This function validates the request data and creates a new user in the database.
     *
     * @param Request $request The HTTP request object containing the user's data.
     * @return \Illuminate\Http\JsonResponse The JSON response with the result of the registration.
     * @throws Exception When there's an error with the server.
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
    /**
     * Reset the user's password.
     *
     * This function resets the user's password and sends a new password to their registered email.
     *
     * @param Request $request The HTTP request object containing the user's email or phone.
     * @return \Illuminate\Http\JsonResponse The JSON response with the result of the password reset.
     * @throws Exception When there's an error with the server.
     */
    function resetpassword(Request $request){
        try{
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'email' => 'nullable|email|exists:users,email', // The user's email is optional, valid and exists in the users table.
                'phone'=>'nullable|exists:users,phone', // The user's phone is optional and exists in the users table.
            ]);

            // If the validation fails, return the errors
            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            // Generate a new random password.
            $password = Str::random(11);

            // Check if the request contains an email.
            if(isset($request->email)){
                // Find the user with the provided email.
                $user = User::where('email', $request->email)->first();
                // Update the user's password.
                $user->password = Hash::make($password);
                $user->save();

                // Send a new password to the user's email.
                Mail::to($request->email)->send(new SendResetPassword($user->name, $password));
            }
            // Check if the request contains a phone.
            elseif(isset($request->phone)){
                // Find the user with the provided phone.
                $user = User::where('phone', $request->phone)->first();
                // Update the user's password.
                $user->password = Hash::make($password);
                $user->save();
            }
            // If neither email nor phone is provided.
            else{
                $this->setMessage('email or phone is required');
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            // Return a success message.
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


    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['facebook', 'google'])) {
            return response()->json(['error' => 'Please login using facebook, or google'], 422);
        }
    }

    /**
     * Redirects the user to the Provider authentication page.
     *
     * @param string $provider The name of the provider.
     * @return JsonResponse A JSON response containing the redirect URL or an error message.
     */
    public function redirectToProvider($provider)
    {
        // Validate the provider.
        $validated = $this->validateProvider($provider);

        // If the provider is not valid, return the JSON response.
        if (!is_null($validated)) {
            return $validated;
        }

        // Redirect the user to the Provider authentication page.
        return Socialite::driver($provider)->redirect();
    }


    /**
     * Social Login
     *
     * This method handles the social login process. It receives the provider name
     * and the access token from the request. It then uses the Socialite library
     * to get the user information from the provider's server. If the user exists
     * in our server, it checks if there is a social login record for the user and
     * the specified provider. If not, it creates a new record. It then creates
     * a new token for the user to be able to login. Finally, it returns the
     * token and the user data in the response.
     *
     * @param Request $request The HTTP request object.
     * @return JsonResponse The JSON response containing the token and user data.
     */
    public function socialLogin(Request $request)
    {
        // Get the provider name and access token from the request.
        $provider = $request->input('provider_name');
        $token = $request->input('access_token');

        // Get the provider's user information (In the provider server).
        $providerUser = Socialite::driver($provider)->user();

        // Search for a user in our server with the specified provider id and provider name.
        $user = User::where('email', $providerUser->getEmail())->first();

        // If the user exists in our server.
        if ($user) {
            // Check if there is a social login record for the user and the specified provider.
            $social = UserSocial::where('user_id', $user->id)
                ->where('provider', $provider)
                ->where('provider_id', $providerUser->getId())
                ->first();

            // If there is no social login record, create a new one.
            if (!$social) {
                $socialCreate = new UserSocial();
                $socialCreate->user_id = $user->id;
                $socialCreate->provider = $provider;
                $socialCreate->provider_id = $providerUser->getId();
                $socialCreate->save();
            }
        }
        // If the user does not exist in our server, create a new user.
        else {
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

        // Create a new token for the user to be able to login.
        $token = $user->createToken('social-login-web')->accessToken;

        // Return the token and user data in the response.
        $this->setData(['token' => $token, 'user' => $user]);
        $this->setMessage(__('translate.login_success_message'));
        return $this->returnResponse();
    }
}
