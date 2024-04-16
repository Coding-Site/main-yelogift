<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use App\Traits\APIHandleClass;
use App\Traits\OrderCheckerTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductReviewController extends Controller
{
    use APIHandleClass,OrderCheckerTrait;
    /**
     * Display a listing of the resource.
     */
    public function index($product_id)
    {
        $reviews = ProductReview::where('product_id', $product_id)->get();
        $this->setData($reviews);
        return $this->returnResponse();

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'product_id'=>'required|exists:products,id',
           'rate'=>'required|integer|min:1|max:5',
           'review'=>'required',
        ]);

        if ($validator->fails()) {
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        if(!$this->checkProductOrder($request->product_id)){
            $this->setMessage(__('translate.you_have_not_ordered_this_product'));
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        $review = new ProductReview;
        $review->product_id = $request->product_id;
        $review->rate = $request->rate;
        $review->review = $request->review;
        $review->user_id = auth()->user()->id;
        $review->save();
        $this->setMessage(__('translate.review_created_success'));
        $this->setData($review);
        return $this->returnResponse();
    }
}
