<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\OutgoingManage;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Outgoing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OutgoingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Gate::authorize('haveaccess', 'outgoing.index');
        $outgoings = Outgoing::paginate(15);

        return response()->json([
            'outgoings' => $outgoings,
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
        Gate::authorize('haveaccess', 'outgoing.create');

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
        Gate::authorize('haveaccess', 'outgoing.create');

        $products = $request->products;

        $outgoingRequest = $request->all();

        OutgoingManage::subtractProducts($products);
        $save = OutgoingManage::calculateTotalWithTaxes($outgoingRequest);

        $data = [
            'status' => 'error',
        ];

        if ($save) {
            $outgoing = Outgoing::create($request->all());

            $data = [
                'status' => 'success',
                'message' => 'outgoing created successfully',
                'outgoing' => $outgoing
            ];
        }

        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Outgoing $outgoing)
    {
        Gate::authorize('haveaccess', 'outgoing.show');

        return response()->json([
            'outgoing' => $outgoing,
            'status' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Outgoing $outgoing)
    {
        Gate::authorize('haveaccess', 'outgoing.edit');

        return response()->json([
            'outgoing' => $outgoing,
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
    public function update(Request $request, Outgoing $outgoing)
    {
        Gate::authorize('haveaccess', 'outgoing.edit');

        $oldProducts = $outgoing->products;

        $products = $request->products;

        $outgoingRequest = $request->all();

        OutgoingManage::updateProducts($oldProducts, $products);

        $update = OutgoingManage::calculateTotalWithTaxes($outgoingRequest);
        
        $data = [
            'status' => 'error',
        ];

        if ($update) {
            $outgoing->update($request->all());
            $data = [
                'status' => 'success',
                'message' => 'outgoing created successfully',
                'outgoing' => $outgoing
            ];
        }

        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Outgoing $outgoing)
    {
        Gate::authorize('haveaccess', 'outgoing.destroy');

        $products = $outgoing->products;
        //dd($products);

        OutgoingManage::removeProducts($products);

        $outgoing->delete();

        $outgoings = $outgoing->paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => 'outgoing deleted successfully',
            'outgoings' => $outgoings
        ]);
    }
}
