<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BinanceFee;

class BinanceFeeController extends Controller
{
    public function index()
    {
        $bf = BinanceFee::first();
        return response()->json([
            'data' => $bf,
        ]);
    }

   
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $bf =new BinanceFee();
        $bf->description = $request->description;
        $bf->percent = $request->percent;
        $bf->save();
        return response()->json([
            'message' => 'bf created successfully',
            'data' => $bf,
        ]);
    }

    public function update(Request $request)
    {
        $bf = BinanceFee::first();
        $bf->description = $request->description;
        $bf->percent = $request->percent;
        $bf->save();
       
        return response()->json([
            'message' => 'bf updated successfully',
            'data' => $bf,
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bf = BinanceFee::find($id);
        if($bf){
            $bf->delete();
            return response()->json([
                'message' => 'bf deleted successfully',
                'data' => $bf,
            ],200);
        }
        return response()->json([
            'message' => 'bf not found',
        ],404);
    }
}
