<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductPart;
use App\Models\Cart;
use App\Models\OrderCode;
use App\Models\OrderProduct;
use App\Models\ProductPartCode;
use App\Models\Order;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductPartController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     */
    public function index($product_id)
    {
        $productParts = ProductPart::where('product_id', $product_id)->get();
        $this->setData($productParts);
        return $this->returnResponse();

    }
    public function get($part_id)
    {
        $productPart = ProductPart::find($part_id);
        $this->setData($productPart);
        return $this->returnResponse();

    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'=>'required|exists:products,id',
            'title'=>'required',
            'price_text'=>'nullable',
            'selling_type'=>'required',
            'price'=>'required|numeric|min:0|not_in:0',
            'discount'=>'nullable|numeric|min:0|not_in:0|lt:price',
            'codes'=>'nullable|array',
        ]);
        if($validator->fails()){
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $productPart = new ProductPart();
        $productPart->product_id = $request->product_id;
        $productPart->title = $request->title;
        $productPart->price_text = $request->price_text;
        $productPart->selling_type = $request->selling_type;
        $productPart->price = $request->price;
        if($request->discount){$productPart->discount = $request->discount;}
        $productPart->save();
        if($request->codes){
        foreach($request->codes as $requestCode){
            $code = new ProductPartCode;
            $code->part_id = $productPart->id;
            $code->product_id = $request->product_id;
            $code->code = encrypt($requestCode);
            $code->save();
        }}
        $this->setData($productPart);
        $this->setMessage(__('translate.create_product_part_success'));
        return $this->returnResponse();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'part_id'=>'required|exists:product_parts,id',
            'product_id'=>'required|exists:products,id',
            'title'=>'required',
            'price_text'=>'nullable',
            'selling_type'=>'required',
            'price'=>'required|numeric|min:0|not_in:0',
            'discount'=>'nullable|numeric|min:0|not_in:0|lt:price',
        ]);
        if($validator->fails()){
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $productPart = ProductPart::find($request->part_id);
        $productPart->product_id = $request->product_id;
        $productPart->title = $request->title;
        $productPart->price_text = $request->price_text;
        $productPart->selling_type = $request->selling_type;
        $productPart->price = $request->price;
        $productPart->discount = $request->discount;

        $productPart->save();
        $this->setData($productPart);
        $this->setMessage(__('translate.create_product_part_success'));
        return $this->returnResponse();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($productPart_id)
    {
        $part = ProductPart::find($productPart_id);
        if(!$part){
            $this->setMessage(__('translate.product_part_not_found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $order_products = OrderProduct::where('product_part_id',$part->id)->get();
        foreach($order_products as $order_product){
            $order = Order::find($order_product->order_id);
            if($order->payment_status == 1 and $order->status == 0){
                $this->setMessage('this part has unconfirmed orders, please confirm orders related first');
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
        }
        $orders = [];
        foreach($order_products as $order_product){
            $order = Order::find($order_product->order_id);
            $order_codes = OrderCode::where('order_product_id',$order_product->id)->get();
            $order_product->delete();
            if (!in_array($order, $orders)) {
                $orders[] = $order;
            } 
        }
        foreach($orders as $order){
            $order->delete();
        }
        $carts = Cart::where('product_part_id',$part->id)->delete();
        $codes = ProductPartCode::where('part_id',$part->id)->delete();
        $part->delete();
        $this->setMessage(__('translate.delete_product_part_success'));
        return $this->returnResponse();
    }
}
