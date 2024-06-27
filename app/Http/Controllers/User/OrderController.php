<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\BinanceFee;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderProduct;
use App\Models\PaymentSetting;
use App\Models\ProductPartCode;
use App\Models\OrderCode;
use App\Models\ProductPart;
use App\Traits\APIHandleClass;
use App\Traits\PaymentHandleTrait;
use CryptoPay\Binancepay\BinancePay;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCodesEmail;
use Google\Service\Docs\Response;

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
        $orders = Order::with(['OrderProduct', 'OrderProduct.product','OrderProduct.product_part','OrderProduct.order_code'])
            ->where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();
        foreach($orders as $order) {
                foreach($order->OrderProduct as $product) {
                    foreach($product->order_code as $code) {
                        $code->decrypt_code = decrypt($code->code);
                   };
                 };
            };

        // Set the order data and return the response
        $this->setData($order);
        return $this->returnResponse();
    }
    public function get($id){
        $order = Order::with(['OrderProduct' , 'OrderProduct.product','OrderProduct.product_part','OrderProduct.order_code']) //=> function ($query) { $query->where('quantity', '>', 0);}
        ->where('user_id', auth()->user()->id)
        ->find($id);
        $total_price = 0;
        foreach($order->OrderProduct as $order_product){
            foreach($order_product->order_code as $code) {
                $code->decrypt_code = decrypt($code->code);
           };

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

    public function order_product(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'part_id' => 'required',
            ]);
            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            $part = ProductPart::find($request->part_id);


            $order = new Order();
            $order->user_id = auth()->user()->id;
            $order->name = auth()->user()->name;
            $order->email = auth()->user()->email;
            $order->phone = auth()->user()->phone;
            $order->country = "none";
            $order->price = $part->price - $part->price* $part->discount/100;
            $order->status = 0;
            $order->payment_status = 0;
            $order->payment_id = 0;
            $order->payment_method = "pay";
            $order->currency = "usd";
            $order->save();

            $product = new OrderProduct;
            $product->order_id = $order->id;
            $product->product_id = $part->product_id;
            $product->product_part_id = $request->part_id;
            $product->quantity = 1;
            $product->price = $part->price - $part->price* $part->discount/100;
            $product->save();

            $this->setData(['order'=>$order,'order_product'=>$product,'part'=>$part]);
            $this->setMessage(__('translate.order_success'));
            //Return the JSON response


        } catch (Exception $e) {
            $this->setMessage(__('translate.error_server'));
            $this->setData([
                'error' => $e->getMessage()
            ]);
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
        }
        return $this->returnResponse();

    }
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
            $bf = BinanceFee::first();
            $order->price = $price + $price*$bf->percent/100;
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
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);

        // If the validation fails, return the error response
        if ($validator->fails()) {
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }


        $order = Order::with(['OrderProduct', 'OrderProduct.product'])->find($request->order_id);
        // return Response($order);
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



    private function checkOrderStatus(Request $request)
    {
        // $transaction = order::findOr($request->get('trx-id'), function () {

        // });

        $order = Order::with(['OrderProduct', 'OrderProduct.product','OrderProduct.product_part'])
        ->find($request->get('trx-id'));
        $client = User::find($order->user_id);

        $order_status = (new BinancePay("binancepay/openapi/v2/order/query"))
                            ->query(['merchantTradeNo' => $order->payment_id]);

        // Save transaction status or whatever you like according to the order status
        if($order_status['status'] == 'SUCCESS'){
            $order->payment_status = 1;
            $order->save();
            $notification = new Notification;
            $notification->title = 'payment success';
            $notification->message = 'your order has been paied successfully, wait for confirmation';
            $notification->type = 0;
            $notification->user_id = $order->user_id;
            $notification->save();
            DB::beginTransaction();
            foreach($order->OrderProduct as $order_product){
            if($order_product->product_part->selling_type == 'auto'){
                $count = $order_product->quantity;
                $sending_codes = array();
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
                foreach($order->OrderProduct as $order_product){
                    $codes = OrderCode::where('order_product_id',$order_product->id)->get();
                    $product = Product::find($order_product->product_id);
                    $product_part = ProductPart::find($order_product->product_part_id);
                    array_push($sending_codes, [$codes, $product, $product_part]);
                }
                Mail::to($client->email)->send(new SendCodesEmail($client->name,$sending_codes));
            }

            }
            DB::commit();
        }
        //return redirect()->url('https://yelogift-front.coding-site.com/');
        return redirect()->to('https://yelogift.net/');


        // $transaction->update(['status' => $order_status['data']['status']];
        // dd($order_status);

        // ITS UPTO YOU 😎
    }
    public function currancy(){
        $payment = PaymentSetting::with('currency')->get();
        $this->setData($payment);
        return $this->returnResponse();
    }
    public function getCurrancy($id){
        $payment = PaymentSetting::with('currency')->find($id);
        $this->setData($payment);
        return $this->returnResponse();
    }
    public function pay_by_currancy(Request $request){

        $validator = Validator::make($request->all(), [
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
        $order->invoice = $request->invoice->store('invoices','public');
        $order->payment_status = 1;
        $order->save();
        $notification = new Notification;
        $notification->title = 'invoice sent';
        $notification->message = 'invoice sent successfully, wait for confirmation !';
        $notification->type = 0;
        $notification->user_id = $order->user_id;
        $notification->save();
        $this->setMessage('invoice sent successfully, wait for confirmation !');
        return $this->returnResponse();

    }

    public function attach_payment_id(Request $request){

        $validator = Validator::make($request->all(), [
            'payment_id'=>'required|exists:payment_settings,id',
            'order_id'=>'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $order = Order::find($request->order_id);
        $order->payment_method = "cryptocurrancy";
        $order->payment_id = $request->payment_id;
        $order->save();
        $paymentSetting = PaymentSetting::with('currency')->find($request->payment_id);
        $this->setData([$paymentSetting,$order]);
        $this->setMessage('payment Success !');
        return $this->returnResponse();

    }
    public function cancelOrder($order_id){
        $order = Order::find($order_id);
        if ($order->payment_status == 1){
            return Response('this order is has been paid');
        }
        $order->status = -1;
        $order->save();
        return $this->returnResponse();
    }
    public function deleteOrder($orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if ($order->user_id !== auth()->user()->id) {
            return response()->json(['error' => 'This is not your order'], 403);
        }

        if ($order->payment_status === 1 && $order->status === 0) {
            return response()->json(['error' => 'This order is not confirmed yet'], 422);
        }

        foreach($order->orderProduct as $orderProduct){
            OrderCode::whereOrderProductId($orderProduct->id)->delete();
        }
        $order->orderProduct()->delete();
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}


