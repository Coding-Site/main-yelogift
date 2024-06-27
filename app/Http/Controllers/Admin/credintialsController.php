<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\APIHandleClass;
use App\Models\Credential;
use Illuminate\Support\Facades\Validator;

class credintialsController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $credintials = Credential::get();
        $this->setData($credintials);
        return $this->returnResponse();
    }

  
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'nullable', 
            'client_secret' => 'nullable',
            'redirect_url' => 'nullable',
            'scope' => 'nullable',
            'state' => 'nullable',
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
        $credintial = new Credential;

        // Assign the request data to the category model
        $credintial->client_id = $request->client_id;
        $credintial->client_secret = $request->client_secret;
        $credintial->redirect_url = $request->redirect_url;
        $credintial->scope = $request->scope;
        $credintial->state = $request->state;

        // Save the category to the database
        $credintial->save();

        // Set the success message and return the response
        $this->setMessage(__('translate.credintial_store_success'));
        return $this->returnResponse();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $credintial = Credential::find($id);
        if (!$credintial){
            return Response('object not found',404);
        }
        $this->setData($credintial);
        return $this->returnResponse();
    }

    
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'nullable', 
            'client_secret' => 'nullable',
            'redirect_url' => 'nullable',
            'scope' => 'nullable',
            'state' => 'nullable',
        ]);
        $credintial = Credential::find($id);
        if (!$credintial){
            return Response('object not found',404);
        }
        // If the validation fails, return the errors
        if ($validator->fails()) {
            // Set the error message and return the response
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

        $credintial->client_id = $request->client_id;
        $credintial->client_secret = $request->client_secret;
        $credintial->redirect_url = $request->redirect_url;
        $credintial->scope = $request->scope;
        $credintial->state = $request->state;

        // Save the category to the database
        $credintial->save();

        // Set the success message and return the response
        $this->setMessage('updated successfully');
        return $this->returnResponse();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $credintial = Credential::find($id);
        if (!$credintial){
            return Response('object not found',404);
        }
        $credintial->delete();
        $this->setMessage('deleted successfully');
        return $this->returnResponse();
    }
}
