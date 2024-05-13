<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Traits\APIHandleClass;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
class SliderController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sliders = Slider::get();
        $this->setData($sliders);
        return $this->returnResponse();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(),[
                'title' => 'required',
                'image' => 'required|image',
                'description' => 'required',
            ]);

            if($validator->fails()){
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            $slider = new Slider();
            $slider->title = $request->title;
            $slider->description = $request->description;
            $slider->image = $request->image->store('sliders', 'public');
            $slider->save();
            $this->setData($slider);
            $this->setMessage(__('translate.create_slider_success'));
            return $this->returnResponse();
        }catch(Exception $e){
            $this->setMessage($e->getMessage());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

    function show($id){
        try{
            $slider = Slider::find($id);
            $this->setData($slider);
            return $this->returnResponse();
        }catch(Exception $e){
            $this->setMessage($e->getMessage());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try{
            $validator = Validator::make($request->all(),[
                'id'=>'required|exists:sliders',
                'title' => 'nullable',
                'image' => 'nullable',
                'description' => 'nullable',
            ]);

            if($validator->fails()){
                $this->setMessage($validator->errors()->first());
                $this->setStatusCode(400);
                $this->setStatusMessage(false);
                return $this->returnResponse();
            }
            $slider = Slider::find($request->id);
            if($request->title){$slider->title = $request->title;}
            if($request->description){$slider->description = $request->description;}
            if($request->file('image')){
                $image=$slider->image;
                $slider->image = $request->image->store('sliders', 'public');
                Storage::delete('public/'.$image);
            }
            $slider->save();
            $this->setData($slider);
            $this->setMessage(__('translate.update_slider_success'));
            return $this->returnResponse();
        }catch(Exception $e){
            $this->setMessage($e->getMessage());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($slider_id)
    {
        $slider = Slider::find($slider_id);
        if(!$slider){
            $this->setMessage(__('translate.slider_not_found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $slider->delete();
        $this->setMessage(__('translate.delete_slider_success'));
        return $this->returnResponse();
    }
}
