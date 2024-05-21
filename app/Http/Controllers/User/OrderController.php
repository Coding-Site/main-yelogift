<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\PaymentSetting;
use App\Models\ProductPartCode;
use App\Models\OrderCode;
use App\Traits\APIHandleClass;
use App\Traits\PaymentHandleTrait;
use CryptoPay\Binancepay\BinancePay;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Coinremitter\Coinremitter;

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
        $order = Order::with(['OrderProduct', 'OrderProduct.product','OrderProduct.product_part'])
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
        $total_price = 0;
        foreach($order->OrderProduct as $order_product){
            $total_price = $total_price + $order_product->product_part->price * $order_product->quantity;
        }

        $dicount = ($total_price - $order->price ) / $total_price * 100;

        // Set the order data and return the response
        $this->setData(['order'=>$order,'total_price'=>$total_price,'discount'=>$dicount]);
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
                'email' => 'email',
            ]);

            // If the validation fails, return the error response
            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            // Start a database transaction
            // DB::beginTransaction();

            // Retrieve the carts of the authenticated user
            $carts = Cart::where('user_id', auth()->user()->id)->with('product','product_part')->get();
            
            // Calculate the total price of the carts
            // $totalPrice = $carts->sum('product.price');
            $price = 0;
            $order = new Order();
            $order->user_id = auth()->user()->id;
            $order->name = $request->name?$request->name:"none";
            $order->email = $request->email?$request->email:"none";
            $order->phone = $request->phone?$request->phone:"none";
            $order->country = $request->country?$request->country:"none";
            $order->price = $price;
            $order->status = 0;
            $order->payment_status = 0;
            $order->payment_id = 0;
            $order->payment_method = "pay";
            $order->currency = "usd";
            $order->save();
            
            foreach($carts as $cart){
                $product = new OrderProduct;
                $price = $price + ($cart->product_part->price - $cart->product_part->price*$cart->product_part->discount/100) * $cart->quantity;
                $product->order_id = $order->id;
                $product->product_id = $cart->product_id;
                $product->product_part_id = $cart->product_part_id;
                $product->quantity = $cart->quantity;
                $product->price = $cart->product_part->price;
                $product->save();
            }
            $order->price = $price;
            $order->save();
            Cart::where('user_id', auth()->user()->id)->delete();
            
               

            // Commit the database transaction
            DB::commit();
            $orderp = Order::with(['OrderProduct', 'OrderProduct.product','OrderProduct.product_part'])->find($order->id);
            // Set the response data and message
            $this->setData($orderp);
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

        //Return the JSON response
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

        if ($res['status'] and $res['status'] === 'SUCCESS') {
            $this->setData([
                'order'=>$order,
                'pay_data'=>$res,
                'data'=>$data
        ]);
            return $this->returnResponse();
        }

        $this->setMessage($res);
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

    // public function testPay(Request $request)
    // {
    //       $order = Order::with(['OrderProduct', 'OrderProduct.product','OrderProduct.product_part'])
    //     ->find(38);
    //     // return($order);
    //         $order->payment_status = 1;
    //         $order->save();
            
    //         DB::beginTransaction();
    //         foreach($order->OrderProduct as $order_product){
    //         if($order_product->product_part->selling_type == 'auto'){
    //             $count = $order_product->quantity;
    //             for ($i = 1; $i <= $count; $i++) {
    //             $part_code = ProductPartCode::where('part_id',$order_product->product_part->id)
    //             ->where('status', 0)->first();
    //             if($part_code){
    //             $order_code = new OrderCode;
    //             $order_code->order_product_id = $order_product->id;
    //             $order_code->product_part_id = $part_code->part_id;
    //             $order_code->code = $part_code->code;
    //             $order_code->save();
    //             $part_code->status = 1;
    //             $part_code->save();}
    //             }
    //         }
                
    //         }
    //         DB::commit();
    //     }
    

    private function checkOrderStatus(Request $request)
    {
        // $transaction = order::findOr($request->get('trx-id'), function () {

        // });

        $order = Order::with(['OrderProduct', 'OrderProduct.product','OrderProduct.product_part'])
        ->find($request->get('trx-id'));

        $order_status = (new BinancePay("binancepay/openapi/v2/order/query"))
                            ->query(['merchantTradeNo' => $order->merchant_trade_no]);

        // Save transaction status or whatever you like according to the order status
        if($order_status['status'] == 'SUCCESS'){
            $order->payment_status = 1;
            $order->save();
            DB::beginTransaction();
            foreach($order->order_product as $order_product){
            if($order_product->product_part->selling_type == 'auto'){
                $count = $order_product->quantity;
                for ($i = 1; $i <= $count; $i++) {
                $part_code = ProductPartCode::where('part_id',$order_product->product_part->id)
                ->where('status', 0)->first();
                if($part_code){
                $order_code = new OrderCode;
                $order_code->order_product_id = $order_product->id;
                $order_code->product_part_id = $part_code->part_id;
                $order_code->code = $part_code->code;
                $order_code->save();
                $part_code->status = 1;
                $part_code->save();}
                }
            }
                
            }
            DB::commit();
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

    public function pay()
    {
        $busd_wallet = new Coinremitter('BUSD');

        // Set the payment amount and currency
        $amount = 10.88;
        $currency = 'BUSD';

        // Create a new payment order
        $order = [
            'bizType' => 'PAY',
            'data' => [
                'merchantTradeNo' => '9825382937292',
                'totalFee' => $amount,
                'transactTime' => time(),
            ],
        ];

        // Send the payment request to BinancePay
        $response = $busd_wallet->create_invoice($order);

        // Handle the payment response
        // if ($response['status'] == 'SUCCESS') {
            return response(['success',$response]);
        // } else {
            return response(['fail',$response]);
        // }
    }

}
