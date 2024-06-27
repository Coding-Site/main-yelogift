<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Exception;
use App\Traits\APIHandleClass;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    use APIHandleClass;
    /**
     * Retrieves all categories and returns a response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Retrieve all categories from the database
        $categories = Category::with(['products' => function ($query) {
            $query->orderBy('category_order', 'asc');
        }])->orderBy('order', 'asc')->get();

        // Set the data to be returned in the response
        $this->setData($categories);

        // Return the response
        return $this->returnResponse();
    }

    public function get($id){
        $category = Category::with(['products' => function ($query) {
            $query->orderBy('category_order', 'asc');
        }])->find($id);
        $this->setData($category);
        return $this->returnResponse();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'order' => 'nullable',
            'name' => 'required|unique:categories', // Category name must be unique
            'icon' => 'required|image', // Category icon is required
        ]);

        // If the validation fails, return the errors
        if ($validator->fails()) {
            // Set the error message and return the response
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $maxIndex = Category::max('order');


        // Create a new category instance
        $category = new Category;

        // Assign the request data to the category model
        $category->name = $request->name;
        $category->icon = $request->icon->store('categories', 'public');
        if($request->order){$category->order = $request->order;}
        else{
            $category->order = $maxIndex+1;
            $category->save();
            
        }

        // Save the category to the database
        $category->save();

        // Set the success message and return the response
        $this->setMessage(__('translate.category_store_success'));
        return $this->returnResponse();
    }

    /**
     * Update the specified resource in storage.
     *
     * This function is used to update a category in the database.
     * It validates the request data and then finds the category
     * with the given category_id. If the category is found,
     * it updates the name and icon fields and saves the changes.
     * Finally, it sets a success message and returns a response.
     *
     * @param Request $request The HTTP request containing the category_id, name and icon fields.
     * @return \Illuminate\Http\JsonResponse The HTTP response.
     */
    public function update(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id', // Category id must exist in the categories table
            'name' => 'nullable|unique:categories', // Category name must be unique
            'icon' => 'nullable', 
            'order' => 'nullable'
        ]);

        // If the validation fails, return the errors
        if ($validator->fails()) {
            // Set the error message and return the response
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        // Find the category with the given category_id
        $category = Category::find($request->category_id);

        // Update the category name and icon
        if($request->name){$category->name=$request->name;}
        if($request->order){$category->order=$request->order;}
        if($request->file('icon')){
            $icon=$category->icon;
            $category->icon = $request->icon->store('categories', 'public');
            Storage::delete('public/'.$icon);

        }
        // Save the changes to the database
        $category->save();

        // Set the success message and return the response
        $this->setMessage(__('translate.category_update_success'));
        return $this->returnResponse();
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  Category  $category The category to be deleted.
     * @return \Illuminate\Http\JsonResponse The HTTP response.
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        // Check if the category exists
        if (!$category) {
            // Set the error message and return the response
            $this->setMessage(__('translate.category_not_found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        // Delete the category from the database
        $category->delete();

        // Set the success message and return the response
        $this->setMessage(__('translate.category_delete_success'));
        return $this->returnResponse();
    }

    public function ordering(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'order' => 'required', 
        ]);

        // If the validation fails, return the errors
        if ($validator->fails()) {
            // Set the error message and return the response
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        
        $category = Category::findOrFail($id);
        DB::beginTransaction();

        try {
        if($category->order > $request->order){
            $categories = Category::whereBetween('order', 
            [$request->order, $category->order-1])->get();
            return Response($categories);
            foreach($categories as $c){
                $c->order =$c->order + 1;
            }

        }else if($category->order < $request->order){
            $categories = Category::whereBetween('order', 
            [$category->order+1, $request->order])->get();
            return Response($categories);
            foreach($categories as $c){
                $c->order =$c->order - 1;
            }
        }
        $category->order = $request->order;
        $category->save();
        // foreach ($categories as $c) {
        //     $c->save();
        // }
        DB::commit();
        $this->setMessage('reorder success');
        return $this->returnResponse();
    } catch (Exception $e) {
        DB::rollBack();
        $this->setMessage('Reorder failed');
        $this->setStatusCode(500);
        $this->setStatusMessage(false);
        return $this->returnResponse();
    }
    }
}
