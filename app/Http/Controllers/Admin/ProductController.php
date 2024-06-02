<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderCode;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductPart;
use App\Models\ProductPartCode;
use App\Traits\APIHandleClass;
use Google\Service\Docs\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    use APIHandleClass;
    /**
     * Retrieves a list of all products.
     *
     * This function uses the Product model to fetch all products from the database.
     * The fetched products are then set as the data for the API response.
     * Finally, the function returns the API response.
     *
     * @return \Illuminate\Http\JsonResponse
     * The API response containing the list of products.
     */
    public function index()
    {
        // Fetch all products from the database
        $products = Product::with('category','product_parts')->get();

        // Set the fetched products as the data for the API response
        $this->setData($products);

        // Return the API response
        return $this->returnResponse();
    }

    function get($id){
        $product = Product::with('category','product_parts')->find($id);
        $this->setData($product);
        return $this->returnResponse();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request The request object containing the data for the new product.
     * @return \Illuminate\Http\JsonResponse The API response containing the success message.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required', // The name of the product is required
            'description' => 'required', // The description of the product is required
            'how_to_redeem' => 'nullable',
            "category_id"=>'required|exists:categories,id', // The category ID of the product is required and must exist in the categories table
            "price"=>'nullable|min:0.00|not_in:0', // The price of the product is required, must be a positive number, and cannot be 0
            "image"=>'required|image', // The image of the product is required and must be an image file
            "discount"=>'nullable|min:0.00|not_in:0', // The discount of the product is required, must be a positive number, and cannot be 0
        ]);

        // If the validation fails, return the errors
        if ($validator->fails()) {
            // Set the error message and return the response
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        // Create a new product instance
        $product = new Product;

        // Set the properties of the product
        $product->name = $request->name;
        $product->description =  $request->description;
        $product->how_to_redeem =  $request->how_to_redeem;
        $product->category_id = $request->category_id;
        if($request->price){
            $product->price = $request->price;
        }else{
            $product->price = 0;
        }
        if($request->discount){
            $product->discount = $request->discount;
        }else{
            $product->discount = 0;
        }

        // Store the image file and set the image path
        $product->image = $request->image->store('products','public');

        // Save the product to the database
        $product->save();

        // Set the success message and return the response
        $this->setMessage(__('translate.Product_store_success'));
        return $this->returnResponse();
    }
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request The request object containing the data for the updated product.
     * @return \Illuminate\Http\JsonResponse The API response containing the success message.
     */
    public function update(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'product_id'=>'required|exists:products,id',  // The product ID of the product is optional and must exist in the products table
            'name' => 'nullable',                           // The name of the product is optional
            'description' => 'nullable', 
            'how_to_redeem' => 'nullable',                   // The description of the product is optional
            "category_id"=>'nullable|exists:categories,id', // The category ID of the product is optional and must exist in the categories table
            "image"=>'nullable',                      // The image of the product is optional and must be an image file
            'price'=>'nullable|numeric',
            'discount'=>'nullable|numeric'
        ]);

        // If the validation fails, return the errors
        if ($validator->fails()) {
            // Set the error message and return the response
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        // Find the product to be updated
        $product = Product::find($request->product_id);

        // Update the properties of the product
        if($request->name){$product->name = $request->name;}
        if($request->description){$product->description = $request->description;}
        if($request->how_to_redeem){$product->how_to_redeem =  $request->how_to_redeem;}
        if($request->price){$product->price = $request->price;}
        if($request->discount){$product->discount = $request->discount;}
        if($request->category_id){$product->category_id = $request->category_id;}
        // If an image is provided, update the image path
        if($request->file('image')){
            $image=$product->image;
            $product->image = $request->image->store('products','public');
            Storage::delete('public/'.$image);
        }

        // Save the updated product to the database
        $product->save();

        // Set the success message and return the response
        $this->setMessage(__('translate.Product_update_success'));
        return $this->returnResponse();
    }

    /**
     * Delete a specific product from the database.
     *
     * @param int $product_id The id of the product to be deleted.
     *
     * @return \Illuminate\Http\JsonResponse The API response.
     */
    public function destroy($product_id)
    {
        // Find the product by its id
        $product = Product::find($product_id);

        // If the product is not found, set an error message and return the response
        if (!$product) {
            $this->setMessage(__('translate.Product_not_found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $order_products = OrderProduct::where('product_id',$product->id)->get();
        foreach($order_products as $order_product){
            $order = Order::find($order_product->order_id);
            if($order->payment_status == 1 and $order->status == 0){
                $this->setMessage('this product has unconfirmed orders, please confirm orders related first');
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
        $carts = Cart::where('product_id',$product->id)->delete();
        $parts = ProductPart::where('product_id',$product->id)->get();
        foreach($parts as $part){
            $codes = ProductPartCode::where('part_id',$part->id)->delete();
            $part->delete();
        }
        
        // Delete the product from the database
        $product->delete();

        // Set a success message and return the response
        $this->setMessage(__('translate.Product_delete_success'));
        return $this->returnResponse();
    }
}
