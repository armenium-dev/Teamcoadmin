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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('info','API\InfoController@getProducts')->name('info.products');
Route::get('availability/{id}','API\InfoController@checkAvailability')->name('info.availability');
Route::get('colors','API\ServicesController@getColors')->name('colors');
Route::get('product/{id}','API\ServicesController@getProduct')->name('product');
Route::post('image','API\ServicesController@image')->name('image');
Route::get('products/{token}','API\TokenController@index')->name('token.index');
Route::get('getToken','API\TokenController@create')->name('token');
Route::get('deleteToken/{id}','API\TokenController@create')->name('token.delete');
Route::post('storeProduct','API\TokenController@storeProduct')->name('token.store-product');
Route::get('deleteProduct/{id}','API\TokenController@DeleteProduct')->name('token.delete-product');
Route::get('deleteProductToken/{id}','API\TokenController@DeleteProductToken')->name('token.delete-product-token');
Route::get('getStates','API\ServicesController@getCountryStates')->name('info.get-states');
Route::get('getSizes','API\ServicesController@getSizes')->name('sizes');
Route::get('getShippingServices','API\ServicesController@getShippingServices')->name('services.services');
Route::post('getShippingRates','API\ServicesController@getShippingRates')->name('services.rates');
Route::get('getShippingFormFields','API\ServicesController@getShippingFormFields')->name('services.fields');
Route::get('getRosterStaticFiles','API\ServicesController@getRosterStaticFiles')->name('services.files');
Route::get('getQuoteRequestLabels','API\ServicesController@getQuoteRequestLabels')->name('services.quote.labels');


Route::post('quotes','API\QuotesController@create')->name('quotes.create');
Route::post('roster','API\RosterController@create')->name('quotes.create');
Route::post('design','API\DesignController@create')->name('design.create');
