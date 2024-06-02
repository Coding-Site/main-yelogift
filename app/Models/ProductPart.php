<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPart extends Model
{
    use HasFactory;
    function codes()
    {
        return $this->hasMany(ProductPartCode::class);
    }
}
