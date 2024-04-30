<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    function product()
    {
        return $this->belongsTo(Product::class);
    }
    function product_part(){
        return $this->belongsTo(ProductPart::class);
    }
}
