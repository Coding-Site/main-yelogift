<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use APIHandleClass;
    /**
     * Retrieves all carts for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(){
        // Retrieve all carts for the authenticated user.
        // The 'auth' helper function is used to get the authenticated user's id.
        $carts = Cart::where('user_id', auth('web')->user()->id)->with('product','product_part')->get();

        // Set the data to be sent in the response.
        $this->setData($carts);

        // Return the response.
        return $this->returnResponse();
    }
    /**
     * Store a new cart item for the authenticated user.
     *
     * @param Request $request The HTTP request object.
     *
     * @return \Illuminate\Http\JsonResponse The HTTP response.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_part_id'=>'required|exists:product_parts,id',
            'quantity' => 'required|min:1|numeric',
        ]);

        // If the validation fails, return the error message
        if ($validator->fails()) {
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        // Check if the cart item already exists for the user and the product
        $cart = Cart::where('user_id', auth()->user()->id)
            ->where('product_id', $request->product_id)->where('product_part_id',$request->product_part_id)
            ->first();

        // If the cart item exists, update the quantity, otherwise create a new cart item
        if ($cart) {
            $cart->quantity = $cart->quantity + $request->quantity;
        } else {
            $cart = new Cart;
            $cart->user_id = auth()->user()->id;
            $cart->product_id = $request->product_id;
            $cart->product_part_id = $request->product_part_id;
            $cart->quantity = $request->quantity;
        }

        // Save the cart item
        $cart->save();

        // Set the response data and message
        $this->setData($cart);
        $this->setMessage(__('translate.Add_to_cart_success'));

        // Return the response
        return $this->returnResponse();
    }
    /**
     * Update a cart item.
     *
     * @param Request $request The HTTP request object.
     *
     * @return \Illuminate\Http\JsonResponse The HTTP response.
     */
    function update(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(),[
            'cart_id' => 'required|exists:carts,id', // Validate that the cart_id exists in the carts table
            'quantity' => 'required|min:1', // Validate that the quantity is provided and is greater than 0
        ]);

        // If the validation fails, return the error message
        if ($validator->fails()) {
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        // Find the cart item by id
        $cart = Cart::find($request->cart_id);

        // Update the quantity of the cart item
        $cart->quantity = $request->quantity;

        // Save the updated cart item
        $cart->save();

        // Set the response data and message
        $this->setMessage(__('translate.update_cart_success'));
        $this->setData($cart);

        // Return the response
        return $this->returnResponse();
    }
    /**
     * Delete a cart item.
     *
     * @param int $id The ID of the cart item to delete.
     *
     * @return \Illuminate\Http\JsonResponse The HTTP response.
     */
    function destroy($id)
    {
        // Find the cart item by ID
        $cart = Cart::find($id);

        // If the cart item does not exist, return an error message
        if (!$cart) {
            $this->setMessage(__('translate.cart_not_found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        // Delete the cart item
        $cart->delete();

        // Set the success message and return the response
        $this->setMessage(__('translate.cart_deleted_success'));
        return $this->returnResponse();
    }

}
