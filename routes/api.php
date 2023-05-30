<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('throttle:500,1,search')->name('api.')->group(function () {
    Route::post('events/search', 'API\EventController@search')->name('events.search');
    Route::post('person/search', 'API\PersonController@search')->name('person.search');
    Route::post('place/search', 'API\PlaceController@search')->name('place.search');
    Route::post('object/search', 'API\ItemController@search')->name('object.search');
});

Route::middleware('auth:sanctum', 'throttle:60,1,default')->name('api.')->group(function () {
    Route::post('events/bulk_acquisition', 'API\EventController@storeBulkAcquitisions')->name('events.bulk_acquisition');
    Route::apiResource('events', 'API\EventController');
    Route::apiResource('person', 'API\PersonController');
    Route::apiResource('place', 'API\PlaceController');
    Route::apiResource('object', 'API\ItemController');
});
