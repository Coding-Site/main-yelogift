<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Subscription;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use APIHandleClass;
    /**
     * Retrieve a random set of popular products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function popular()
    {
        // Retrieve popular products from the database, using eager loading to reduce the number of queries
        // $populars = Product::where('popular', 1)->get();
        $populars = Product::with('category')->inRandomOrder()->get();

        // Set the data to be returned in the response
        $this->setData($populars);

        // Return the response
        return $this->returnResponse();
    }
    /**
     * Retrieve all categories with their associated products.
     *
     * This function retrieves all categories from the database, using eager loading to
     * reduce the number of queries. The retrieved categories along with their associated
     * products are then set as the data for the API response. Finally, the function returns
     * the API response.
     *
     * @return \Illuminate\Http\JsonResponse The API response containing the list of
     *                                       categories with their associated products.
     */
    public function category()
    {
        // Retrieve all categories from the database, using eager loading to reduce the number of queries
        // The 'products' relationship is loaded to fetch the products associated with each category
        $categories = Category::with('products')->get();

        // Set the data to be returned in the response
        $this->setData($categories);

        // Return the response
        return $this->returnResponse();
    }
    public function getCategory($category_id){
        // Retrieve all categories from the database, using eager loading to reduce the number of queries
        // The 'products' relationship is loaded to fetch the products associated with each category
        $categories = Category::with('products')->find($category_id);

        // Set the data to be returned in the response
        $this->setData($categories);

        // Return the response
        return $this->returnResponse();
    }
    /**
     * Retrieve all products with their associated categories.
     *
     * This function retrieves all products from the database, using eager loading to
     * reduce the number of queries. The retrieved products along with their associated
     * categories are then set as the data for the API response. Finally, the function returns
     * the API response.
     *
     * @return \Illuminate\Http\JsonResponse
     * The API response containing the list of products with their associated categories.
     */
    public function product(){
        // Retrieve all products from the database, using eager loading to reduce the number of queries
        // The 'category' relationship is loaded to fetch the categories associated with each product
        $products = Product::with('category')->inRandomOrder()->paginate(10);

        // Set the data to be returned in the response
        $this->setData($products);

        // Return the response
        return $this->returnResponse();
    }

    /**
     * Retrieve a specific product with its associated category and parts.
     *
     * This function retrieves a specific product from the database, using eager loading to
     * reduce the number of queries. The retrieved product along with its associated category
     * and parts are then set as the data for the API response. Finally, the function returns
     * the API response.
     *
     * @param int $product_id The ID of the product to retrieve
     * @return \Illuminate\Http\JsonResponse The API response containing the product, its
     *                                       associated category, and parts.
     */
    public function getProduct($product_id){
        // Retrieve a specific product from the database, using eager loading to reduce the number of queries
        // The 'category' relationship is loaded to fetch the category associated with the product
        // The 'product_parts' relationship is loaded to fetch the parts associated with the product
        $product = Product::with('category','product_parts')->find($product_id);
        foreach($product->product_parts as $part){
            $part->priceDiscount = round($part->price - $part->discount,2);

        }

        // Set the data to be returned in the response
        $this->setData($product);

        // Return the response
        return $this->returnResponse();
    }
    /**
     * Retrieve all sliders from the database.
     *
     * This function retrieves all sliders from the database and sets them as the data
     * for the API response. Finally, the function returns the API response.
     *
     * @return \Illuminate\Http\JsonResponse The API response containing the list of sliders.
     */
    public function slider(){
        // Retrieve all sliders from the database
        $slider = Slider::all();
        // Set the data to be returned in the response
        $this->setData($slider);
        // Return the response
        return $this->returnResponse();
    }

    public function subscribe(Request $request){
        $subscribe = new Subscription;
        $subscribe->email = $request->email;
        $subscribe->save();
        $this->setMessage(__('translate.subscribe_success'));
        return $this->returnResponse();
    }


}
