<?php

namespace App\Http\Controllers;

use App\Models\Advertisment;
use Illuminate\Http\Request;

class AdvertismentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ads = Advertisment::all();
        return response()->json([
            'data' => $ads,
        ]);
    }

   
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $description = $request->description;
        $url = $request->url;
        $ad = Advertisment::firstOrCreate([], [
            'description' => $description,
            'url' => $url,
        ]);

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
        $ad = Advertisment::find($id);
        if($ad){
        return response()->json([
            'data' => $ad,
        ],200);
        }
        return response()->json([
            'message' => 'ad not found',
        ],404);

    }

    
    public function update(Request $request)
    {
        $ad = Advertisment::first();
        if($ad){
            $ad->description = $request->description;
            $ad->url = $request->url;
            $ad->save();
            
        }else{
            $ad = new Advertisment;
            $ad->description = $request->description;
            $ad->url = $request->url;
            $ad->save();
            
        }
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
        $ad = Advertisment::find($id);
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
