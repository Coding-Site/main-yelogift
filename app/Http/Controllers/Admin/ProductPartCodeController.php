<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPart;
use App\Models\ProductPartCode;
use App\Models\Setting;
use App\Traits\APIHandleClass;
use App\Traits\OrderCheckerTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ProductPartCodeController extends Controller
{
    use APIHandleClass,OrderCheckerTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $password = Setting::where('key', 'password_code')->first();
        if(!$password){
            Setting::create([
                'key' => 'password_code',
                'value' => encrypt($request->password)
            ]);
        }
        if( decrypt($password->value) == $request->password){
            $codes = ProductPartCode::where('part_id', $request->part_id)->get();
            foreach($codes as $code){
                $code->code = decrypt($code->code);
            }
            $this->setData($codes);
            return $this->returnResponse();
        }
        $this->setMessage(__('translate.Unauthorized'));
        $this->setStatusCode(401);
        $this->setStatusMessage(false);
        return $this->returnResponse();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'part_id'=>'required|exists:product_parts,id',
           'code'=>'required',
        ]);

        if ($validator->fails()) {
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        if($this->checkCodeIsFound($request->code)){
            $this->setMessage(__('translate.code_is_founded'));
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $code = new ProductPartCode;
        $code->part_id = $request->part_id;
        $code->product_id = ProductPart::find($request->part_id)->product_id;
        $code->code = encrypt($request->code);
        $code->save();
        $this->setMessage(__('translate.Product Part Code Added Successfully'));
        return $this->returnResponse();
    }


    public function uploadCodes(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'part_id'=>'required|exists:product_parts,id',
           'codesFile'=>'required|file',
        ]);
        if ($validator->fails()) {
            $this->setMessage($validator->errors()->first());
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $product_id = ProductPart::find($request->part_id)->product_id;
        $file = $request->file('codesFile');
        $extension = $file->getClientOriginalExtension();
        if ($extension === 'csv') {
            $codes = fopen($file, 'r');
            while (($line = fgetcsv($codes)) !== false) {
                $code = new ProductPartCode;
                $code->part_id = $request->part_id;
                $code->product_id = $product_id;
                $code->code = encrypt($line[0]);
                $code->save();
            }
        } elseif ($extension === 'xlsx') {
            Excel::load($file->getPathname(), function($reader) use ($request,$product_id) {
                // Loop through each row of the Excel file
                $reader->each(function($row) use ($request,$product_id) {
                    // Assuming you want only the first column
                    $code = new ProductPartCode;
                    $code->product_id = $product_id;
                    $code->part_id = $request->part_id;
                    $code->code = encrypt($row->get(0));
                    $code->save();
                });
            });
        } elseif ($extension === 'txt') {
            $contents = file($file);
            $contents = array_map('trim', $contents); // Trim whitespace from each line
            foreach($contents as $content) {
                $code = new ProductPartCode;
                $code->product_id = $product_id;
                $code->part_id = $request->part_id;
                $code->code = encrypt($content);
                $code->save();
            }
        } else {
            $this->setMessage(__('translate.not_support_file'));
            $this->setStatusCode(400);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $this->setMessage(__('translate.Product Part Codes Added Successfully'));
        return $this->returnResponse();

    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($productPartCode_id)
    {
        $code = ProductPartCode::find($productPartCode_id);
        if(!$code){
            $this->setMessage(__('translate.Product Part Code Not Found'));
            $this->setStatusCode(404);
            $this->setStatusMessage(false);
            return $this->returnResponse();
        }
        $code->delete();
        $this->setMessage(__('translate.Product Part Code Deleted Successfully'));
        return $this->returnResponse();
    }
}
