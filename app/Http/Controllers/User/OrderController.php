<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Traits\APIHandleClass;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use APIHandleClass;
    public function index(){
        $order = Order::with(['OrderProduct','OrderProduct.product'])->where('user_id',auth()->user()->id)->get();
        $this->setData($order);
        return $this->returnResponse();
    }
    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                "name"=>"required",
                'email'=>'required|email',
                "phone"=>'required',
                "country"=>'required'
            ]);

            if($validator->fails()){
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            DB::beginTransaction();
            $carts = Cart::with('product')->where('user_id', auth()->user()->id)->get();
            $totalPrice = $carts->sum('product.price');
            $order = new Order();
            $order->user_id = auth()->user()->id;
            $order->name = $request->name;
            $order->email = $request->email;
            $order->phone = $request->phone;
            $order->country = $request->country;
            $order->price = $totalPrice;
            $order->status = 0;
            $order->payment_status = 0;
            $order->payment_id = 0;
            $order->payment_method = "pay";
            $order->currency = "usd";
            $order->save();

            $products = new OrderProduct;
            foreach ($carts as $cart){
                $products->order_id = $order->id;
                $products->product_id = $cart->product_id;
                $products->quantity = $cart->quantity;
                $products->price = $cart->product->price;
                $products->save();
            }

            DB::commit();
            $this->setData($order);
            $this->setMessage(__('translate.order_success'));
            return $this->returnResponse();

        }catch(Exception $e){
            DB::rollBack();
            $this->setMessage(__('translate.error_server'));
            $this->setData([
                'error' => $e->getMessage()
            ]);
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();

        }
    }

}
