<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaticPageController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     *
     * This function retrieves all the static_pages from the database and returns
     * them in a response.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing
     * the list of static_pages.
     */
    public function index()
    {
        // Retrieve all the static_pages from the database
        $static_pages = StaticPage::get();

        // Set the retrieved static_pages as the data for the response
        $this->setData($static_pages);

        // Return the JSON response
        return $this->returnResponse();
    }

    /**
     * Store a newly created resource in storage.
     *
     * This function validates the request data and saves a new page to the database.
     * If the data is valid, it returns a JSON response with the newly created page.
     * If the data is not valid, it returns a JSON response with the validation errors.
     * If an exception occurs, it returns a JSON response with an error message.
     *
     * @param  \Illuminate\Http\Request  $request The HTTP request object containing the page data.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the newly created page or the validation errors.
     */
    public function store(Request $request)
    {
        try{
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'title' => 'required', // The title field is required
                'content'=>'required' // The content field is required
            ]);

            // If the validation fails, return the errors
            if ($validator->fails()) {
                // Set the error message and return the response
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }


            // Create a new page object and save it to the database
            $page = new StaticPage;
            $page->title = $request->title; // Set the title of the page
            $page->content = $request->content; // Set the content of the page
            $page->save(); // Save the page to the database

            // Set success message and data for the response
            $this->setMessage(__('translate.page_store_success'));
            $this->setData($page);
            return $this->returnResponse();
        }catch(\Exception $e){
            // Set error message and return the response
            $this->setMessage(__('translate.error_server'));
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

    /**
     * Display the specified resource.
     *
     * This function retrieves a page from the database based on the provided
     * Page model instance and returns it.
     *
     * @param  \App\Models\Page  $page The Page model instance representing the page to be displayed.
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        $page = StaticPage::find($id);
        $this->setData($page);
        return $this->returnResponse();

    }

    /**
     * Update the specified resource in storage.
     *
     * This function validates the request data and updates an existing page in the database.
     * If the data is valid, it returns a JSON response with the updated page.
     * If the data is not valid, it returns a JSON response with the validation errors.
     * If an exception occurs, it returns a JSON response with an error message.
     *
     * @param  \Illuminate\Http\Request  $request The HTTP request object containing the page data.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the updated page or the validation errors.
     */
    public function update(Request $request)
    {
        try{
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'page_id'=>'required|exists:static_pages,id', // The page_id field is required and must exist in the static_pages table
                'title' => 'required', // The title field is required
                'content'=>'required' // The content field is required
            ]);

            // If the validation fails, return the errors
            if ($validator->fails()) {
                // Set the error message and return the response
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            // Find the page to be updated
            $page = StaticPage::find($request->page_id);

            // Update the page attributes
            $page->title = $request->title;
            $page->content = $request->content;

            // Save the updated page to the database
            $page->save();

            // Set success message and data for the response
            $this->setMessage(__('translate.page_update_success'));
            $this->setData($page);
            return $this->returnResponse();
        }catch(\Exception $e){
            // Set error message and return the response
            $this->setMessage(__('translate.error_server'));
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $page_id The ID of the page to be deleted.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the
     * success message or the error message.
     */
    public function destroy($page_id)
    {
        //
    }

}
