<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;

class StaticController extends Controller{

	private function getRosterStaticFile($id){
		$res = '';
		$roster_form_files_options = Settings::get('roster_form_files_options');

		if(!empty($roster_form_files_options)){
			$roster_form_files_options = json_decode($roster_form_files_options, true);
			foreach($roster_form_files_options as $option){
				if($id == $option['id']){
					$res = 'storage/'.$option['file'];
				}
			}
		}

		return $res;
	}

	public function getStaticFile(Request $request){
		$id = str_replace('-', '_', $request->segment(2));

		return redirect($this->getRosterStaticFile($id));
	}


}
