<?php
namespace App\Traits;

use App\Models\OrderCode;

trait AuthHandleTrait
{
    /**
     * This function takes a login and password and determines if it is an email
     * or a phone number and returns an associative array with the corresponding key.
     *
     * @param string $login The login string to be validated.
     * @param string $password The password string.
     *
     * @return array An associative array with 'email' or 'phone' as the key and
     *               the login string as the value and the password string as the value.
     */
    public function type_credential($login, $password){
        // Check if the login is a valid email address
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            // If it is, return an associative array with 'email' as the key and the login as the value
            return ['email' => $login, 'password'=>$password];
        }
        // If it is not, return an associative array with 'phone' as the key and the login as the value
        return ['phone'=>$login,'password'=>$password];
    }

    public function UniqeCode($code){
       $allOrderCodes = OrderCode::all();

       foreach ($allOrderCodes as $orderCode) {
           $decodedCode = decrypt($orderCode->code);
           if ($decodedCode == $code) {
              return 0;
           }
           return 1;
       }
    }

}
