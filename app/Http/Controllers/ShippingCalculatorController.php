<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShippingCalculatorController extends Controller{

	public function __construct(){
		$this->middleware('auth');
	}

	/**
	 * Display a listing of the resource.
	 * @return \Illuminate\Http\Response
	 */
	public function index(){
		$ship_engine_jersey_type_options = Settings::get('ship_engine_jersey_type_options');
		$ship_engine_province_codes = Settings::get('ship_engine_province_codes');

		if(!is_null($ship_engine_province_codes)){
			$ship_engine_province_codes = explode(',', $ship_engine_province_codes);
		}else{
			$ship_engine_province_codes = [];
		}

		if(!is_null($ship_engine_jersey_type_options)){
			$ship_engine_jersey_type_options = json_decode($ship_engine_jersey_type_options, true);
		}else{
			$ship_engine_jersey_type_options = [];
		}
		
		return view('shipping.index', [
			'ship_engine_jersey_type_options' => $ship_engine_jersey_type_options,
			'ship_engine_province_codes' => $ship_engine_province_codes,
		]);
	}
	
}
