<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index')->name('home');

Route::get('person/{person}/export/{format}', 'PersonController@export')->name('person.export');
Route::resource('person', 'PersonController');

Route::get('item/{item}/export/{format}', 'ItemController@export')->name('item.export');
Route::resource('item', 'ItemController');

Route::get('object/{object}/export/{format}', 'ArtefactController@export')->name('object.export');
Route::resource('object', 'ArtefactController');

Route::get('place/{place}/export/{format}', 'PlaceController@export')->name('place.export');
Route::resource('place', 'PlaceController');

Route::get('event/export/{format}', 'EventController@export')->name('event.export');
Route::get('event/{type?}', 'EventController@index')->name('event.index');
Route::resource('event', 'EventController')->only(['show']);

Route::group(['middleware' => 'auth'], function() {
    Route::resource('event', 'EventController')->only(['destroy']);
});

Route::redirect('/admin', '/events/event');

Route::get('/ontology/rdf', function () {
    return response()
        ->view('export.rdf.index')
        ->header('Content-Type', 'application/xml');
});
Route::view('/ontology', 'ontology');

Auth::routes(['register' => false]);
