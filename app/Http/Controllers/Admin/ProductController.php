<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $products = Product::get();

        // Set the fetched products as the data for the API response
        $this->setData($products);

        // Return the API response
        return $this->returnResponse();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            "category_id"=>'required|exists:categories,id',
            "price"=>'required|min:0.00|not_in:0',
            "image"=>'required|image',
            "discount"=>'required|min:0.00|not_in:0',
        ]);

        // If the validation fails, return the errors
        if ($validator->fails()) {
            // Set the error message and return the response
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $product = new Product;
        $product->name = $request->name;
        $product->description =  $request->description;
        $product->category_id = $request->category_id;
        $product->price = $request->price;
        $product->image = $request->image->store('products','public');
        $product->discount = $request->discount;
        $product->save();
        $this->setMessage(__('translate.Product_store_success'));
        return $this->returnResponse();
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'=>'required|exists:products,id',
            'name' => 'required',
            'description' => 'required',
            "category_id"=>'required|exists:categories,id',
            "price"=>'required|min:0.00|not_in:0',
            "image"=>'nullable|image',
            "discount"=>'required|min:0.00|not_in:0',
        ]);

        // If the validation fails, return the errors
        if ($validator->fails()) {
            // Set the error message and return the response
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $product = Product::find($request->product_id);
        $product->name = $request->name;
        $product->description =  $request->description;
        $product->category_id = $request->category_id;
        $product->price = $request->price;
        if($request->hasFile('image')){
            $product->image = $request->image->store('products','public');
        }
        $product->discount = $request->discount;
        $product->save();
        $this->setMessage(__('translate.Product_update_success'));
        return $this->returnResponse();
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  Product  $product The product to be deleted.
     */
    public function destroy($product_id)
    {
        // Check if the product exists
        $product = Product::find($product_id);
        // return $product;
        if (!$product) {
            // Set the error message and return the response
            $this->setMessage(__('translate.Product_not_found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        // Delete the product
        $product->delete();

        // Set the success message and return the response
        $this->setMessage(__('translate.Product_delete_success'));
        return $this->returnResponse();
    }
}
