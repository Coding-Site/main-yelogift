<?php
namespace App\Traits;

use App\Models\Order;
use App\Models\ProductPartCode;

trait OrderCheckerTrait
{
    function checkProductOrder($product_id){
        $user = auth()->user()->id;
        $orders = Order::with('OrderProduct')->where('user_id',$user)->get();
        foreach($orders as $order){
            foreach($order->OrderProduct as $product){
                if($product->product_id == $product_id){
                    return true;
                }
            }
        }
        return false;
    }
    function checkCodeIsFound($code){
        $all_code = ProductPartCode::all();
        foreach($all_code as $code){
            if(decrypt($code->code) == $code){
                return true;
            }
        }
        return false;

    }

}
