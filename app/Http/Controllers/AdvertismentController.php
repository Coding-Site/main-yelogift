<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use Illuminate\Http\Request;

class AdvertismentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ads = Advertisement::all();
        return response()->json([
            'data' => $ads,
        ]);
    }

   
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $ad =new Advertisement();
        $ad->description = $request->description;
        $ad->url = $request->url;
        $ad->color1 = $request->color1;
        $ad->color2 = $request->color2;
        $ad->save();
        return response()->json([
            'message' => 'ad description created successfully',
            'data' => $ad,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ad = Advertisement::find($id);
        if($ad){
        return response()->json([
            'data' => $ad,
        ],200);
        }
        return response()->json([
            'message' => 'ad not found',
        ],404);

    }

    
    public function update(Request $request, $id)
    {
        $ad = Advertisement::find($id);
        $ad->description = $request->description;
        $ad->url = $request->url;
        $ad->color1 = $request->color1;
        $ad->color2 = $request->color2;
        $ad->save();
       
        return response()->json([
            'message' => 'ad updated successfully',
            'data' => $ad,
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ad = Advertisement::find($id);
        if($ad){
            $ad->delete();
            return response()->json([
                'message' => 'ad deleted successfully',
                'data' => $ad,
            ],200);
        }
        return response()->json([
            'message' => 'ad not found',
        ],404);
    }
}
