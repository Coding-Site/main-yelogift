<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderCode;
use App\Models\OrderProduct;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with(['OrderProducts.product','OrderProducts.order_code'])->get();
        $orders->each(function ($order) {
            $order->OrderProducts->each(function ($product) {
                $product->order_code->each(function ($code) {
                    $code->code = decrypt($code->code);
                });
            });
        });

        $this->setData($orders);
        return $this->returnResponse();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function delivery_code(Request $request){

        $validator = Validator::make($request->all(), [
            'order_product_id' => 'required|exists:orders,id',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $order_product = OrderProduct::find($request->order_product_id);
        $order_code = OrderCode::where('order_product_id',$request->order_product_id)->count();
        if($order_code >= $order_product->quantity){
            $this->setMessage(__('translate.order_code_limitation'));
            $this->setStatusCode('400');
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $order_code_unique = OrderCode::where('code',encrypt($request->code))->first();
        if($order_code_unique){
            $this->setMessage(__('translate.order_code_found_later'));
            $this->setStatusCode('400');
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $order = new OrderCode;
        $order->order_product_id = $request->order_product_id;
        $order->code = encrypt($request->code);
        $order->save();
        $this->setMessage(__('translate.order_code_store_success'));
        $this->setData($order);
        return $this->returnResponse();
    }
}
