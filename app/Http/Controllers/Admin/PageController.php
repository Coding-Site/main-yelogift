<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pages = Page::get();
        $this->setData($pages);
        return $this->returnResponse();
     }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'slug' => 'required',
                'content'=>'required'
            ]);

            // If the validation fails, return the errors
            if ($validator->fails()) {
                // Set the error message and return the response
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            $page = new Page;
            $page->title = $request->title;
            $page->slug = $request->slug;
            $page->content = $request->content;
            $page->save();
            $this->setMessage(__('translate.page_store_success'));
            $this->setData($page);
            return $this->returnResponse();
        }catch(\Exception $e){
            $this->setMessage(__('translate.error_server'));
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'page_id'=>'required|exists:pages,id',
                'title' => 'required',
                'slug' => 'required',
                'content'=>'required'
            ]);

            // If the validation fails, return the errors
            if ($validator->fails()) {
                // Set the error message and return the response
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }

            $page = Page::find($request->page_id);
            $page->title = $request->title;
            $page->slug = $request->slug;
            $page->content = $request->content;
            $page->save();
            $this->setMessage(__('translate.page_update_success'));
            $this->setData($page);
            return $this->returnResponse();
        }catch(\Exception $e){
            $this->setMessage(__('translate.error_server'));
            $this->setStatusCode(500);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($page_id)
    {
        $page = Page::find($page_id);
        if(!$page){
            $this->setMessage(__('translate.page_not_found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $page->delete();
        $this->setMessage(__('translate.page_delete_success'));
        return $this->returnResponse();


    }
}
