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
    /**
     * Retrieves the orders for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Retrieve the orders with their related products for the authenticated user
        $order = Order::with(['OrderProduct', 'OrderProduct.product'])
            ->where('user_id', auth()->user()->id)
            ->get();

        // Set the order data and return the response
        $this->setData($order);
        return $this->returnResponse();
    }
    public function get($id){
        $order = Order::with(['OrderProduct', 'OrderProduct.product','OrderProduct.product_part'])
        ->where('user_id', auth()->user()->id)
        ->find($id);

        // Set the order data and return the response
        $this->setData($order);
        return $this->returnResponse();
    }
    /**
     * Store a new order for the authenticated user.
     *
     * @param Request $request The HTTP request object.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                "name" => "required",
                'email' => 'required|email',
                "phone" => 'required',
                "country" => 'required'
            ]);

            // If the validation fails, return the error response
            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            // Start a database transaction
            DB::beginTransaction();

            // Retrieve the carts of the authenticated user
            $carts = Cart::with('product')->where('user_id', auth()->user()->id)->get();

            // Calculate the total price of the carts
            $totalPrice = $carts->sum('product.price');

            // Create a new order
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

            // Create order products for each cart
            $products = new OrderProduct;
            foreach ($carts as $cart) {
                $products->order_id = $order->id;
                $products->product_id = $cart->product_id;
                $products->product_part_id = $cart->product_part_id;
                $products->quantity = $cart->quantity;
                $products->price = $cart->product->price;
                $products->save();
            }

            // Commit the database transaction
            DB::commit();

            // Set the response data and message
            $this->setData($order);
            $this->setMessage(__('translate.order_success'));

        } catch (Exception $e) {
            // Rollback the database transaction and set the error response
            DB::rollBack();
            $this->setMessage(__('translate.error_server'));
            $this->setData([
                'error' => $e->getMessage()
            ]);
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
        }

        // Return the JSON response
        return $this->returnResponse();
    }

}
