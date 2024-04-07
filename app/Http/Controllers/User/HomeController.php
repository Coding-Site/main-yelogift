<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use APIHandleClass;
    public function popular(){
        // $populars = Product::where('popular',1)->get();
        $populars = Product::with('category')->inRandomOrder()->get();
        $this->setData($populars);
        return $this->returnResponse();
    }
    public function category(){
        $category = Category::with('products')->get();
        $this->setData($category);
        return $this->returnResponse();
    }
    public function product(){
        $products = Product::with('category')->inRandomOrder()->get();
        $this->setData($products);
        return $this->returnResponse();
    }
}
