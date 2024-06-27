<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderCode;
use App\Mail\SendCodesEmail;
use App\Models\Notification;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Traits\APIHandleClass;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ProductPart;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     *
     * This function retrieves all the orders with their associated order products and order codes.
     * It decrypts the code of each order code before returning the data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Retrieve all orders with their associated order products and order codes
        $orders = Order::with(['OrderProduct','OrderProduct.product','OrderProduct.order_code','OrderProduct.product_part'])->get();
        
        // Decrypt the code of each order code
        foreach($orders as $order) {
            foreach($order->OrderProduct as $product) {
                foreach($product->order_code as $code) {
                    $code->decrypt_code = decrypt($code->code);
               };
             };
        };

        // Set the data to be returned and return the response
        $this->setData($orders);
        return $this->returnResponse();
    }

    public function get($order_id){
        // Retrieve all orders with their associated order products and order codes
        $orders = Order::with(['OrderProduct','OrderProduct.product','OrderProduct.order_code','OrderProduct.product_part'])->find($order_id);

        // Decrypt the code of each order code
        $orders->each(function ($order) {
            $order->OrderProduct->each(function ($product) {
                $product->order_code->each(function ($code) {
                    $code->decrypt_code = decrypt($code->code);
                });
            });
        });
        $total_price = 0;
        foreach($orders->OrderProduct as $order_product){
            $total_price = $total_price + $order_product->product_part->price * $order_product->quantity;
        }

        $dicount = ($total_price - $orders->price ) / $total_price * 100;
        // Set the data to be returned and return the response
        $this->setData(['order'=>$orders,'total_price'=>$total_price,'discount'=>$dicount]);
        return $this->returnResponse();
    }
    public function cancelOrder($order_id){
        $order = Order::find($order_id);
        // if ($order->payment_status == 1){
        //     return Response('this order is has been paid');
        // }
        $order->status = -1;
        $order->save();
        $notification = new Notification;
        $notification->title = 'cancel order';
        $notification->message = 'your order has been cancelled';
        $notification->type = 0; 
        $notification->user_id = $order->user_id;
        $notification->save();
        return $this->returnResponse();
    }
    public function deleteOrder($order_id){
        $order = Order::find($order_id);
        if ($order->payment_status == 1 and $order->status == 0){
            return Response('this order not confirmed yet');
        }
        $order->delete();
        return Response('order deleted');
    }



    /**
     * Store a newly created resource in storage.
     *
     * This function handles the delivery of a code for an order product.
     * It validates the request, checks for any limitations, and stores the code.
     *
     * @param Request $request The HTTP request object.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response.
     */
    public function delivery_code(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'order_id'=>'required|exists:orders,id',
            'order_codes'=>'nullable|array',
            'order_codes.*.order_product_id' => 'nullable|exists:order_products,id',
            'order_codes.*.product_part_id' => 'nullable|exists:product_parts,id',
            'order_codes.*.code' => 'nullable',
        ]);

        // If the validation fails, return the error response
        if ($validator->fails()) {
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $confirmed_order = Order::with('OrderProduct')->find($request->order_id);
        if($confirmed_order->payment_status == 1){
        DB::beginTransaction();
        
        foreach($request->order_codes as $order_code){
            // Get the order product and the count of order codes
            $order_product = OrderProduct::find($order_code['order_product_id']);
            $order_code_count = OrderCode::where('order_product_id', $order_code['order_product_id'])->count();

            // If the order code count is greater than or equal to the quantity, return an error response
            if ($order_code_count >= $order_product->quantity) {
                $this->setMessage(__('translate.order_code_limitation'));
                $this->setStatusCode('400');
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            $order_code_unique = OrderCode::where('code', encrypt($order_code['code']))->first();
            if ($order_code_unique) {
                $this->setMessage(__('translate.order_code_found_later'));
                $this->setStatusCode('400');
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            // Create a new order code and save it
            $order = new OrderCode;
            $order->order_product_id = $order_code['order_product_id'];
            $order->product_part_id = $order_code['product_part_id'];
            $order->code = encrypt($order_code['code']);
            $order->save();
        }
        DB::commit();
    }else{
        $this->setMessage('The order has not been paid yet');
        $this->setStatusCode('400');
        $this->setStatusMessage(false);
        return $this->returnResponse();
    }
    $confirmed_order->status = 1;
    $confirmed_order->save();
    $sending_codes = array();
    foreach($confirmed_order->orderProduct as $order_product){
        $codes = OrderCode::where('order_product_id',$order_product->id)->get();
        $product = Product::find($order_product->product_id);
        $product_part = ProductPart::find($order_product->product_part_id);
        array_push($sending_codes, [$codes, $product, $product_part]);
    }
        $client = User::find($confirmed_order->user_id);
        
        $notification = new Notification;
        $notification->title = 'codes sent';
        $notification->message = 'order confirmed and your codes sent to your email, check your inbox';
        $notification->type = 0; 
        $notification->user_id = $client->id;
        $notification->save();
        // Set the success response
        Mail::to($client->email)->send(new SendCodesEmail($client->name,$sending_codes));
        $this->setMessage('Email sent successfully');
        return $this->returnResponse();
    }
}
