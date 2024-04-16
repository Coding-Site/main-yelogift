<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductPartCode;
use App\Models\Setting;
use App\Traits\APIHandleClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ProductPartCodeController extends Controller
{
    use APIHandleClass;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if( decrypt(Setting::where('key', 'password_code')->first()->value) == $request->password){
            $codes = ProductPartCode::where('part_id', $request->part_id)->get();
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
        $code = new ProductPartCode;
        $code->part_id = $request->part_id;
        $code->code = $request->code;
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

        $file = $request->file('codesFile');
        $extension = $file->getClientOriginalExtension();
        if ($extension === 'csv') {
            $codes = fopen($file, 'r');
            while (($line = fgetcsv($codes)) !== false) {
                $code = new ProductPartCode;
                $code->part_id = $request->part_id;
                $code->code = $line[0];
                $code->save();
            }
        } elseif ($extension === 'xlsx') {
            Excel::load($file->getPathname(), function($reader) use ($request) {
                // Loop through each row of the Excel file
                $reader->each(function($row) use ($request) {
                    // Assuming you want only the first column
                    $code = new ProductPartCode;
                    $code->part_id = $request->part_id;
                    $code->code = $row->get(0);
                    $code->save();
                });
            });
        } elseif ($extension === 'txt') {
            $contents = file($file);
            $contents = array_map('trim', $contents); // Trim whitespace from each line
            foreach($contents as $content) {
                $code = new ProductPartCode;
                $code->part_id = $request->part_id;
                $code->code = $content;
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
