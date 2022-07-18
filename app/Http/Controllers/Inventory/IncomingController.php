<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Incoming;
use App\Models\Inventory\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class IncomingController extends Controller
{
    public function updateProductReceiptsStock($products, $oldProducts, $add, $update)
    {
        if ($add) {

            foreach ($products as $productValue) {
                $product = Product::find($productValue['product_id']);
                $productQuantity = $productValue['quantity'];
                $product->receipts  = $product->receipts + $productQuantity;
                $product->stock  = $product->stock + $productQuantity;
                $product->update();
            }
        } else if ($update && $oldProducts != null) {

            $updateProductsIds = [];

            foreach ($products as $key => $productValue) {
                $updateProductsIds[] = $productValue['product_id'];
                $product = Product::find($productValue['product_id']);
                $productQuantity = $productValue['quantity'];
                $product->receipts  = $product->receipts - $oldProducts[$key]['quantity'] + $productQuantity;
                $product->stock  =  $product->stock - $oldProducts[$key]['quantity'] + $productQuantity;
                $product->update();
            }

            //Modify stock and receipts if the product is remove from the list of products 
            foreach ($oldProducts as $key => $oldProductValue) {
                $oldProductId = $oldProductValue['product_id'];
                $quantityOldProduct = $oldProductValue['quantity'];
                if (!in_array($oldProductId, $updateProductsIds)) {
                    $product = Product::find($oldProductId);
                    $product->stock = $product->stock - $quantityOldProduct;
                    $product->receipts = $product->receipts - $quantityOldProduct;
                    $product->update();
                };
            }
        } else {
            foreach ($products as $productValue) {
                $product = Product::find($productValue['product_id']);
                $productQuantity = $productValue['quantity'];
                $product->receipts  = $product->receipts - $productQuantity;
                $product->stock  = $product->stock - $productQuantity;
                $product->update();
            }
        }
    }
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
        Gate::authorize('haveaccess', 'incoming.create');

        $incoming = Incoming::create($request->all());
        $products = $request->products;

        $this->updateProductReceiptsStock($products, null, true, false);

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

        return response()->json([
            'incoming' => $incoming,
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


        $products = $request->products;

        $oldProducts = $incoming->products;

        $this->updateProductReceiptsStock($products, $oldProducts, false, true);

        $incoming->update($request->all());
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

        $this->updateProductReceiptsStock($products, null, false, false);

        $incoming->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Incoming deleted successfully',
            'incomings' => $incomings
        ]);
    }
}
