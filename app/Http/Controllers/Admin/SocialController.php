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
     */
    public function index()
    {
        $social = Social::get();
        $this->setData($social);
        return $this->returnResponse();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'url' => 'required',
                'icon' => 'required',
            ]);

            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            $social = new Social;
            $social->name = $request->name;
            $social->url = $request->url;
            $social->icon = $request->icon;
            $social->save();
            $this->setData($social);
            $this->setMessage(__('translate.social_store_success'));
            return $this->returnResponse();
        }catch(Exception $e){
            $this->setMessage(__('translate.error_server'));
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Social $social)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Social $social)
    {
        try {
            $validator = Validator::make($request->all(), [
                'social_id'=>'required|exists:socials,id',
                'name' => 'required',
                'url' => 'required',
                'icon' => 'required',
            ]);

            if ($validator->fails()) {
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            $social = Social::find($request->social_id);
            $social->name = $request->name;
            $social->url = $request->url;
            $social->icon = $request->icon;
            $social->save();
            $this->setData($social);
            $this->setMessage(__('translate.social_update_success'));
            return $this->returnResponse();
        }catch(Exception $e){
            $this->setMessage(__('translate.error_server'));
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($social_id)
    {
        $social = Social::find($social_id);
        if(!$social){
            $this->setMessage(__('translate.social_not_found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $social->delete();
        $this->setMessage(__('translate.social_delete_success'));
        return $this->returnResponse();
    }
}
