<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'status' => 'SUCCESS',
            'data' => Place::all()->keyBy('id'),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validatedInput = $request->validate([
            'places' => 'required|array',
            'places.*.id' => 'integer'
        ]);

        $places = Place::find(Arr::pluck($validatedInput['places'], 'id'));

        return response()->json([
            'status' => 'SUCCESS',
            'data' => $places->keyBy('id'),
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Place  $place
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Place $place)
    {
        return response()->json([
            'status' => 'SUCCESS',
            'data' => $place,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Place  $place
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Place $place)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Place  $place
     * @return \Illuminate\Http\Response
     */
    public function destroy(Place $place)
    {
        //
    }
}
