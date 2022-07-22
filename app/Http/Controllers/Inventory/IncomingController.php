<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\IncomingManage;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Incoming;
use App\Models\Inventory\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class IncomingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Gate::authorize('haveaccess', 'document.index');
        $incomings = Incoming::paginate(15);

        return response()->json([
            'incomings' => $incomings,
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
        $products = Product::all();

        return response()->json([
            'status' => 'success',
            'products' => $products
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
        Gate::authorize('haveaccess', 'incoming.create');

        $products = $request->products;
        $requestIncoming = $request->all();

        IncomingManage::addProducts($products);
        $save = IncomingManage::calculateTotal($requestIncoming);

        if ($save) {
            $incoming = Incoming::create($request->all());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Incoming created successfully',
            'incoming' => $incoming
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Incoming $incoming)
    {
        Gate::authorize('haveaccess', 'incoming.show');

        return response()->json([
            'incoming' => $incoming,
            'status' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Incoming $incoming)
    {
        Gate::authorize('haveaccess', 'incoming.edit');
        $products = Product::all();

        return response()->json([
            'incoming' => $incoming,
            'products' => $products,
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
    public function update(Request $request, Incoming $incoming)
    {
        Gate::authorize('haveaccess', 'incoming.edit');

        $requestIncoming = $request->all();

        $products = $request->products;

        $oldProducts = $incoming->products;

        IncomingManage::updateProducts($oldProducts, $products);

        $update = IncomingManage::calculateTotal($requestIncoming);

        if ($update) {
            $incoming->update($request->all());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Incoming updated successfully',
            'incoming' => $incoming
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Incoming $incoming)
    {
        Gate::authorize('haveaccess', 'incoming.destroy');
        $incomings = $incoming->paginate(15);

        $products = $incoming->products;

        IncomingManage::removeProducts($products);

        $incoming->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Incoming deleted successfully',
            'incomings' => $incomings
        ]);
    }
}
