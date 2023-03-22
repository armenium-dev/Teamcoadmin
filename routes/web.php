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

use App\Http\Shopify\Shopify;
use App\Http\SVG\arraysHelpers;
use App\client;
use App\quote;
use App\roster;
use App\design;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminMailable;
use App\Mail\ClientMailable;
use App\Mail\RosterAdminMailable;
use App\Mail\RosterClientMailable;
use Illuminate\Support\Facades\Route;

/*Route::get('/builder/shopify', function () {
	return response('Тестовый маршрут', 200);
});*/


Route::get('/', function(){
	return view('welcome');
});

Route::middleware('auth')->group(function(){
	Route::get('list-users', 'Auth\RegisterController@index')->name('list.user');
	Route::post('registerUsers', 'Auth\RegisterController@store')->name('register.user');
	Route::get('list-user/{id}/edit', 'Auth\RegisterController@edit')->name('edit.user');
	Route::delete('delete-user/{id}', 'Auth\RegisterController@destroy')->name('destroy.user');
	Route::match(['PUT', 'PATCH'], 'users-update/{user}', 'Auth\RegisterController@update')->name('update.user');

	Route::get('/home', 'HomeController@index')->name('home');
	Route::post('/color/order', 'ColorController@orderColors')->name('color.order');
	Route::resource('/color', 'ColorController');
	Route::match(['PUT', 'PATCH'], '/builder/updateFileSVG/{builder}', 'BuilderController@updateFileSVG')->name('builder.updatefilesvg');
	Route::match(['PUT', 'PATCH'], '/builder/updateColors/{builder}', 'BuilderController@updateColors')->name('builder.updatecolors');
	Route::match(['PUT', 'PATCH'], '/builder/updateColorSets/{builder}', 'BuilderController@updateColorSets')->name('builder.updatecolorsets');
	Route::match(['PUT', 'PATCH', 'POST'], '/builder/ajaxUpdateFields', 'BuilderController@ajaxUpdateFields')->name('builder.ajaxupdatefields');
	Route::get('/builder/shopify', 'BuilderController@shopify')->name('builder.shopify');
	Route::get('/quotes/parts', 'QuoteController@parts')->name('quote.parts');
	Route::get('/roster/parts', 'RosterController@parts')->name('roster.parts');
	Route::get('/design/parts', 'DesignController@parts')->name('design.parts');
	Route::get('/settings/parts', 'SettingsController@parts')->name('settings.parts');
	Route::get('/logger/parts', 'LoggerController@parts')->name('logger.parts');
	Route::post('/logger/check', 'LoggerController@checkOne')->name('logger.check');
	Route::get('/static/view-sample', 'StaticController@getStaticFile');
	Route::get('/static/artwork-placement-guide', 'StaticController@getStaticFile');
	Route::get('/static/excel-roster-form', 'StaticController@getStaticFile');
	Route::post('/quotes/resend', 'QuoteController@resend')->name('quote.resend');
	#Route::get('/quotes/test', 'QuoteController@test')->name('quote.test');
	Route::resource('/builder', 'BuilderController');
	Route::resource('/sizes', 'SizeController');
	Route::resource('quotes', 'QuoteController');
	Route::resource('roster', 'RosterController');
	Route::resource('design', 'DesignController');
	Route::resource('logger', 'LoggerController');
	Route::resource('settings', 'SettingsController');
	Route::resource('shipping', 'ShippingCalculatorController');

	Route::get('/testEmails/admin/{id}', function($id){

		$quote = quote::find($id);
		$products = arraysHelpers::returnProducts($quote->styles);
		$data = [
			'quote' => $quote,
			'customerShopify' => 'No',
			'products' => $products
		];

		///dd($data);
		return new AdminMailable($data);

	})->name('test.emails.admin');

	Route::get('/testEmails/client/{id}', function($id){
		$quote = quote::find($id);
		$products = arraysHelpers::returnProducts($quote->styles);
		$data = [
			'quote' => $quote,
			'customerShopify' => 'No',
			'products' => $products
		];

		return new ClientMailable($data);
	})->name('test.emails.client');

	Route::get('/testEmails/adminRoster/{id}', function($id){
		$roster = roster::find($id);
		$data = [
			'roster' => $roster,
			'jersey_detail' => json_decode($roster->jersey->colors)
		];

		return new RosterAdminMailable($data);
	})->name('test.emails.rosterAdmin');

	Route::get('/testEmails/adminClient/{id}', function($id){
		$roster = roster::find($id);
		$data = [
			'roster' => $roster,
			'jersey_detail' => json_decode($roster->jersey->colors)
		];

		return new RosterClientMailable($data);
	})->name('test.emails.rosterClient');
});

Auth::routes();
