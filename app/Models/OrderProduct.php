<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;
    function product(){
        return $this->belongsTo(Product::class);
    }

    function order(){
        return $this->belongsTo(Order::class);
    }
    function order_code(){
        return $this->hasMany(OrderCode::class);
    }
    function product_part(){
        return $this->belongsTo(ProductPart::class);
    }
}
