<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Social;
use App\Traits\APIHandleClass;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SocialController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     *
     * This function retrieves all the social media profiles from the database
     * and returns a response with the data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Retrieve all social media profiles from the database
        $social = Social::get();

        // Set the data to be returned in the response
        $this->setData($social);

        // Return the response
        return $this->returnResponse();
    }

    /**
     * Store a newly created resource in storage.
     *
     * This function validates the request data, creates a new Social instance
     * and saves it to the database. If successful, it sets the data, a success
     * message and returns a response. If an exception occurs, it sets an error
     * message and returns a response.
     *
     * @param Request $request The HTTP request object.
     * @return \Illuminate\Http\JsonResponse The HTTP response containing the data,
     * message and status code.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'name' => 'required', // Name field is required
                'url' => 'required', // URL field is required
                'icon' => 'required', // Icon field is required
            ]);

            // If validation fails, set an error message and return a response
            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            // Create a new instance of Social
            $social = new Social;
            $social->name = $request->name; // Set the name field
            $social->url = $request->url; // Set the URL field
            $social->icon = $request->icon; // Set the icon field
            $social->save(); // Save the Social instance to the database

            // Set the data, a success message and return a response
            $this->setData($social);
            $this->setMessage(__('translate.social_store_success'));
            return $this->returnResponse();
        } catch (Exception $e) {
            // If an exception occurs, set an error message and return a response
            $this->setMessage(__('translate.error_server'));
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

    /**
     * Display the specified resource.
     *
     * This function retrieves a single Social instance from the database based on the
     * provided id in the request. It uses the id to find the corresponding Social
     * instance in the database. If the Social instance is found, it sets the data,
     * a success message, and returns a response. If the Social instance is not found,
     * it sets an error message and returns a response.
     *
     * @param Social $social The Social instance to be displayed.
     * @return \Illuminate\Http\JsonResponse The HTTP response containing the data,
     * message and status code.
     */
    public function show(Social $social)
    {
        // Retrieve a single Social instance from the database

        // Set the data, a success message and return a response
        $this->setData($social);
        $this->setMessage(__('translate.social_show_success'));
        return $this->returnResponse();
    }

    /**
     * Update the specified resource in storage.
     *
     * This function updates a Social instance in the database based on the provided
     * request and instance. It first validates the request data, then finds the
     * Social instance by the provided id, updates its fields, saves it to the
     * database, and returns a response. If any exception occurs, it sets an error
     * message and returns a response.
     *
     * @param Request $request The HTTP request object containing the updated data.
     * @param Social $social The Social instance to be updated.
     * @return \Illuminate\Http\JsonResponse The HTTP response containing the data,
     * message and status code.
     */
    public function update(Request $request, Social $social)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'social_id'=>'required|exists:socials,id',
                'name' => 'required',
                'url' => 'required',
                'icon' => 'required',
            ]);

            // If validation fails, set an error message and return a response
            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            // Find the Social instance by the provided id
            $social = Social::find($request->social_id);

            // Update the instance's fields
            $social->name = $request->name;
            $social->url = $request->url;
            $social->icon = $request->icon;

            // Save the updated instance to the database
            $social->save();

            // Set the data, a success message and return a response
            $this->setData($social);
            $this->setMessage(__('translate.social_update_success'));
            return $this->returnResponse();
        }catch(Exception $e){
            // If an exception occur, set an error message and return a response
            $this->setMessage(__('translate.error_server'));
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * This function finds a Social instance by the provided id,
     * and deletes it from the database. If the Social instance is not found,
     * it sets an error message and returns a response.
     *
     * @param int $social_id The id of the Social instance to be deleted.
     * @return \Illuminate\Http\JsonResponse The HTTP response containing the data,
     * message and status code.
     */
    public function destroy($social_id)
    {
        // Find the Social instance by the provided id
        $social = Social::find($social_id);

        // If the Social instance is not found, set an error message and return a response
        if(!$social){
            $this->setMessage(__('translate.social_not_found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        // Delete the Social instance from the database
        $social->delete();

        // Set the success message and return a response
        $this->setMessage(__('translate.social_delete_success'));
        return $this->returnResponse();
    }
}
