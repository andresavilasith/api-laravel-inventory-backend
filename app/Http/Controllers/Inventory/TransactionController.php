<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Gate::authorize('haveaccess', 'transaction.index');

        $transactions = Transaction::paginate(15);

        return response()->json([
            'transactions' => $transactions,
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
        Gate::authorize('haveaccess', 'transaction.create');

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
        Gate::authorize('haveaccess', 'transaction.create');

        $transaction = Transaction::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction successfully created',
            'transaction' => $transaction
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        Gate::authorize('haveaccess', 'transaction.show');

        return response()->json([
            'transaction' => $transaction,
            'status' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        Gate::authorize('haveaccess', 'transaction.destroy');

        return response()->json([
            'transaction' => $transaction,
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
    public function update(Request $request, Transaction $transaction)
    {
        Gate::authorize('haveaccess', 'transaction.edit');

        $transaction->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction successfully updated',
            'transaction' => $transaction
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        Gate::authorize('haveaccess', 'transaction.destroy');

        $transaction->delete();

        $transactions = Transaction::paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction successfully deleted',
            'transactions' => $transactions
        ]);
    }
}
