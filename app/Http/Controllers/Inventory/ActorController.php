<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Actor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ActorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Gate::authorize('haveaccess', 'actor.index');

        $actors = Actor::paginate(15);

        return response()->json([
            'actors' => $actors,
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
        Gate::authorize('haveaccess', 'actor.create');

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
        Gate::authorize('haveaccess', 'actor.create');

        $actor = Actor::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Actor successfully stored',
            'actor' => $actor
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Actor $actor)
    {
        Gate::authorize('haveaccess', 'actor.show');

        return response()->json([
            'actor' => $actor,
            'status' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Actor $actor)
    {
        Gate::authorize('haveaccess', 'actor.edit');

        return response()->json([
            'actor' => $actor,
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Actor $actor)
    {
        Gate::authorize('haveaccess', 'actor.edit');

        $actor->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Actor successfully updated',
            'actor' => $actor
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Actor $actor)
    {
        Gate::authorize('haveaccess', 'actor.destroy');

        $actor->delete();

        $actors = Actor::paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => 'Actor successfully remove',
            'actors' => $actors
        ]);
    }
}
