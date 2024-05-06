<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\PaymentSetting;
use App\Traits\APIHandleClass;
use App\Traits\PaymentHandleTrait;
use CryptoPay\Binancepay\BinancePay;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use APIHandleClass,PaymentHandleTrait;
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
    public function binance_pay(Request $request){
        $order = Order::with(['OrderProduct', 'OrderProduct.product'])->find($request->order_id);
        // $pay = $this->initiateBinancePay($order->id,'Order From Website','order from '.$order->name.' from email '.$order->email.' by id '.$order->id ,$order->price);


         $user = auth()->user();

        $data['order_amount'] =  $order->price;
        $data['package_id'] = $order->id; // referenceGoodsId: id from the DB Table that user choose to purchase
        $data['goods_name'] = 'Order From Website';
        $data['goods_detail'] = 'order from '.$order->name.' from email '.$order->email.' by id '.$order->id;
        $data['buyer'] = [
            "referenceBuyerId" => $user->id,
            "buyerEmail" => $user->email,
            "buyerName" => [
                "firstName" => $user->name,
                "lastName" => $user->name
            ]
        ];
        $data['trx_id'] = $order->id; // used to identify the transaction after payment has been processed
        $data['merchant_trade_no'] = mt_rand(982538, 9825382937292) ; // Provide an unique code;

        $order->payment_method = "binance";
        $order->payment_id = $data['merchant_trade_no'];
        $order->save();

        $binancePay = new BinancePay("binancepay/openapi/v2/order");
        $res = $binancePay->createOrder($data);

        if ($res['status'] === 'SUCCESS') {
            $this->setData([
                'order'=>$order,
                'pay_data'=>
 $res['data']
        ]);
            return $this->returnResponse();
        }

        $this->setMessage($res['message']);
        $this->setStatusCode(400);
        $this->setStatusMessage(false);
        return $this->returnResponse();

    }

    public function returnCallback(Request $request)
    {
        return $this->checkOrderStatus($request);
    }

    // GET /binancepay/cancelURL
    public function cancelCallback(Request $request)
    {
        return $this->checkOrderStatus($request);
    }

    private function checkOrderStatus(Request $request)
    {
        $transaction = order::findOr($request->get('trx-id'), function () {

        });

        $order_status = (new BinancePay("binancepay/openapi/v2/order/query"))
                            ->query(['merchantTradeNo' => $transaction->merchant_trade_no]);

        // Save transaction status or whatever you like according to the order status
        if($order_status['status'] == 'SUCCESS'){
            $transaction->status = 1;
            $transaction->save();
        }
        return redirect()->url('https://yelogift-front.coding-site.com/');


        // $transaction->update(['status' => $order_status['data']['status']];
        // dd($order_status);

        // ITS UPTO YOU 😎
    }
    public function currancy(){
        $payment = PaymentSetting::all();
        $this->setData($payment);
        return $this->returnResponse();
    }
    public function pay_by_currancy(Request $request){

        $validator = Validator::make($request->all(), [
            'currency_id'=>'required|exists:payment_settings,id',
            'order_id'=>'required|exists:orders,id',
            'invoice'=>'required|file'
        ]);

        if ($validator->fails()) {
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $order = Order::find($request->order_id);
        $order->payment_method = "currancy";
        $order->payment_id = $request->currency_id;
        $order->invoice = $request->invoice->store('invoice');
        $order->save();
        $this->setMessage('payment Success !');
        return $this->returnResponse();

    }

}
