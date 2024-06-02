<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPartCode extends Model
{
    use HasFactory;
    function part()
    {
        return $this->belongsTo(ProductPart::class);
    }
    function product()
    {
        return $this->belongsTo(Product::class);
    }
}
