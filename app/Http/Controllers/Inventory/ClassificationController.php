<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Classification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClassificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Gate::authorize('haveaccess', 'classification.index');

        $classifications = Classification::paginate(15);

        return response()->json([
            'classifications' => $classifications,
            'status' => 'success'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Gate::authorize('haveaccess', 'classification.create');

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Gate::authorize('haveaccess', 'classification.create');

        $classification = Classification::create($request->all());

        return response()->json([
            'classification' => $classification,
            'status' => 'success',
            'message' => 'Classification stored successfully'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Classification $classification)
    {
        Gate::authorize('haveaccess', 'classification.show');

        return response()->json([
            'classification' => $classification,
            'status' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Classification $classification)
    {
        Gate::authorize('haveaccess', 'classification.edit');

        return response()->json([
            'classification' => $classification,
            'status' => 'success'
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Classification $classification)
    {
        Gate::authorize('haveaccess', 'classification.edit');

        $classification->update($request->all());

        return response()->json([
            'status' => 'successfully',
            'message' => 'Classification successfully updated',
            'classification' => $classification,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Classification $classification)
    {
        Gate::authorize('haveaccess', 'classification.destroy');
        
        $classification->delete();
        $classifications = Classification::paginate(15);

        return response()->json([
            'status' => 'successfully',
            'message' => 'Classification successfully deleted',
            'classifications' => $classifications,
        ]);
    }
}
