<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TableFooter;

class TableFooterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $footer = TableFooter::first();
        return response()->json([
            'data' => $footer,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $description = $request->description;
        $tableFooter = TableFooter::firstOrCreate([], [
            'description' => $description,
        ]);

        return response()->json([
            'message' => 'footer description created successfully',
            'data' => $tableFooter,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $description = $request->description;
        $tableFooter = TableFooter::firstOrCreate([], [
            'description' => $description,
        ]);
        $tableFooter->description = $description;
        $tableFooter->save();
        return response()->json([
            'message' => 'footer description updated successfully',
            'data' => $tableFooter,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
