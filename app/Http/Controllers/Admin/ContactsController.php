<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Traits\APIHandleClass;
use Illuminate\Support\Facades\Validator;

class ContactsController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = Contact::get();
        $this->setData($contacts);
        return $this->returnResponse();
    }

  
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'nullable', 
            'mail_1' => 'nullable|email',
            'mail_2' => 'nullable|email',
            'phone_1' => 'nullable|min:10|max:50',
            'phone_2' => 'nullable|min:10|max:50',
            'whatsapp' => 'nullable|min:10|max:50',
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
        $contact = new Contact;

        // Assign the request data to the category model
        $contact->address = $request->address;
        $contact->mail_1 = $request->mail_1;
        $contact->mail_2 = $request->mail_2;
        $contact->phone_1 = $request->phone_1;
        $contact->phone_2 = $request->phone_2;
        $contact->whatsapp = $request->whatsapp;

        // Save the category to the database
        $contact->save();

        // Set the success message and return the response
        $this->setMessage(__('translate.contact_store_success'));
        return $this->returnResponse();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contact = Contact::find($id);
        if (!$contact){
            return Response('object not found',404);
        }
        $this->setData($contact);
        return $this->returnResponse();
    }

    
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'nullable', 
            'mail_1' => 'nullable|email',
            'mail_2' => 'nullable|email',
            'phone_1' => 'nullable|min:10|max:50',
            'phone_2' => 'nullable|min:10|max:50',
            'whatsapp' => 'nullable|min:10|max:50',
        ]);
        $contact = Contact::find($id);
        if (!$contact){
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

        $contact->address = $request->address;
        $contact->mail_1 = $request->mail_1;
        $contact->mail_2 = $request->mail_2;
        $contact->phone_1 = $request->phone_1;
        $contact->phone_2 = $request->phone_2;
        $contact->whatsapp = $request->whatsapp;

        // Save the category to the database
        $contact->save();

        // Set the success message and return the response
        $this->setMessage('updated successfully');
        return $this->returnResponse();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $contact = Contact::find($id);
        if (!$contact){
            return Response('object not found',404);
        }
        $contact->delete();
        $this->setMessage('deleted successfully');
        return $this->returnResponse();
    }
}
