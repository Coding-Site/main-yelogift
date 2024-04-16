<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductPart;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductPartController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     */
    public function index($product_id)
    {
        $productParts = ProductPart::where('product_id', $product_id)->get();
        $this->setData($productParts);
        return $this->returnResponse();

    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'=>'required|exists:products,id',
            'title'=>'required',
            'price'=>'required|numeric|min:0|not_in:0',
        ]);
        if($validator->fails()){
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $productPart = new ProductPart();
        $productPart->product_id = $request->product_id;
        $productPart->title = $request->title;
        $productPart->price = $request->price;
        $productPart->save();
        $this->setData($productPart);
        $this->setMessage(__('translate.create_product_part_success'));
        return $this->returnResponse();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'part_id'=>'required|exists:product_parts,id',
            'product_id'=>'required|exists:products,id',
            'title'=>'required',
            'price'=>'required|numeric|min:0|not_in:0',
        ]);
        if($validator->fails()){
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $productPart = ProductPart::find($request->part_id);
        $productPart->product_id = $request->product_id;
        $productPart->title = $request->title;
        $productPart->price = $request->price;
        $productPart->save();
        $this->setData($productPart);
        $this->setMessage(__('translate.create_product_part_success'));
        return $this->returnResponse();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($productPart_id)
    {
        $part = ProductPart::find($productPart_id);
        if(!$part){
            $this->setMessage(__('translate.product_part_not_found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $part->delete();
        $this->setMessage(__('translate.delete_product_part_success'));
        return $this->returnResponse();
    }
}
