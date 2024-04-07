<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $categories = Category::get();

        // Set the data to be returned in the response
        $this->setData($categories);

        // Return the response
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

        // Create a new category instance
        $category = new Category;

        // Assign the request data to the category model
        $category->name = $request->name;
        $category->icon = $request->icon->store('categories', 'public');

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
            'name' => 'required|unique:categories', // Category name must be unique
            'icon' => 'nullable|image', // Category icon is required
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
        $category->name = $request->name;
        if($request->hasFile('icon')){
            $category->icon = $request->icon->store('categories', 'public');

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
}
