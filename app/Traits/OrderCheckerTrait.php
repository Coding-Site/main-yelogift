<?php
namespace App\Traits;

use App\Models\Order;

class OrderCheckerTrait
{
    function checkOrder($product_id){
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

}
