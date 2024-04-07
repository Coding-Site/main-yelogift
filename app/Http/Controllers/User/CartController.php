<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use APIHandleClass;
    public function index(){
        $carts = Cart::where('user_id', auth('')->user()->id)->get();
        $this->setData($carts);
        return $this->returnResponse();
    }
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'product_id'=>'required|exists:products,id',
            'quantity'=>'required|min:1|numeric'
        ]);

        if($validator->fails()){
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        $cart = Cart::where('user_id', auth()->user()->id)->where('product_id', $request->product_id)->first();
        if($cart){
            $cart->quantity = $cart->quantity + $request->quantity;
        }else{
            $cart = new Cart;
            $cart->user_id = auth()->user()->id;
            $cart->product_id = $request->product_id;
            $cart->quantity = $request->quantity;
        }
        $cart->save();
        $this->setData($cart);
        $this->setMessage(__('translate.Add_to_cart_success'));
        return $this->returnResponse();
    }
    function update(Request $request){
        $validator = Validator::make($request->all(),[
            'cart_id'=>'required|exists:carts,id',
            'quantity'=>'required|min:1'
        ]);
        if($validator->fails()){
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        $cart = Cart::find($request->cart_id);
        $cart->quantity = $request->quantity;
        $cart->save();
        $this->setMessage(__('translate.update_cart_success'));
        $this->setData($cart);
        return $this->returnResponse();
    }
    function destroy($id){
        $cart = Cart::find($id);
        if(!$cart){

            $this->setMessage(__('translate.cart_not_found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $cart->delete();
        $this->setMessage(__('translate.cart_deleted_success'));
        return $this->returnResponse();
    }
}
