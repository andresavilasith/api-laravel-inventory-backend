<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Gate::authorize('haveaccess', 'document.index');
        $documents = Document::paginate(15);

        return response()->json([
            'documents' => $documents,
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
        Gate::authorize('haveaccess', 'document.create');

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
        Gate::authorize('haveaccess', 'document.create');

        Document::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Document created successfully'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Document $document)
    {
        Gate::authorize('haveaccess', 'document.show');

        return response()->json([
            'document' => $document,
            'status' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Document $document)
    {
        Gate::authorize('haveaccess', 'document.edit');
        
        return response()->json([
            'document' => $document,
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
    public function update(Request $request, Document $document)
    {
        Gate::authorize('haveaccess', 'document.edit');

        $document->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Document updated successfully',
            'document' => $document
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Document $document)
    {
        Gate::authorize('haveaccess', 'document.destroy');
        
        $document->delete();

        $documents = $document->paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => 'Document deleted successfully',
            'documents' => $documents
        ]);
    }
}
