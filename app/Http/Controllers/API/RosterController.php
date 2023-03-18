<?php

namespace App\Http\Controllers\API;

use App\client;
use App\Http\Controllers\Controller;
use App\Http\SVG\arraysHelpers;
use App\Mail\RosterAdminMailable;
use App\Mail\RosterClientMailable;
use App\quanity;
use App\roster;
use App\Size;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RosterController extends Controller {
	
	private $debug = false;
	
	public function create(Request $request){
		#dd($request->user);
	    if($this->debug){
		    dd([
			    $request->Roster['comments'],
			    #$request->user,
			    #$request->designDetails,
			    #$request->files,
		    ]);
		    
	    }
	
	    //Log::stack(['single'])->debug(var_export($_POST, true));
	    //return response()->json(['data'=>$_POST, 'message' => 'success' ],200);

	    Log::stack(['custom'])->debug('// BEGIN _________________________________________');
	    Log::stack(['custom'])->debug(__CLASS__);

	    $ordered_sizes = [];
	    $colors_sizes = [];
	    $Sizes = Size::orderBy('weight')->get();
	    foreach($Sizes as $size){
		    $ordered_sizes[$size->name] = $size->weight;
		    $colors_sizes[$size->name] = $size->color;
	    }
	    unset($Sizes, $size);
		
	    
    	$client = client::create($request->user);
		$billing = $client->billing()->create($request->billing);
    	$roster = $client->roster()->create($request->Roster);

	    Log::stack(['custom'])->debug('Roster Form #'.$roster->id);

	    // Данные из секции формы 2. Jersey Details
	    $detail = $roster->jersey()->create([
		    'style_code' => $request->jerseyDetails['style_code'],
		    'colors'     => (isset($request->jerseyDetails['colors'])) ? json_encode($request->jerseyDetails['colors']) : ''
	    ]);

	    // Данные из секции формы 5. Jersey Quantities (Количество верхней одежды)
        if(isset($request->quantity['quantity'])){
	        $dataQty = [];
	        $qty = 0;
			foreach($request->quantity['quantity'] as $key => $quantity){
				$dataQty[$key] = [
					'quantity' => $quantity,
					'size'     => $request->quantity['size'][$key],
					'type' => 'top'
				];
				$qty += intval($quantity);
			}
	        /*$_dataQty = $dataQty;
	        $dataQty = [];
			foreach($ordered_sizes as $_size => $weight){
				foreach($_dataQty as $key => $_data){
					if($_data['size'] == $_size){
						$dataQty[] = $_data;
					}
				}
			}
			unset($_dataQty);*/
	        $roster->quantities()->createMany($dataQty);
	        $roster->top_quantity = $qty;
	    }

	    // Данные из секции формы 6. Shorts Quantities (Количество шортов)
        if(isset($request->quantity_s['quantity_s'])){
	        $dataQty = [];
	        $qty = 0;
			foreach($request->quantity_s['quantity_s'] as $key => $quantity){
				$dataQty[$key] = [
					'quantity' => $quantity,
					'size'     => $request->quantity_s['size_s'][$key],
					'type' => 'short'
				];
				$qty += intval($quantity);
			}
	        /*$_dataQty = $dataQty;
	        $dataQty = [];
	        foreach($ordered_sizes as $_size => $weight){
		        foreach($_dataQty as $key => $_data){
			        if($_data['size'] == $_size){
				        $dataQty[] = $_data;
			        }
		        }
	        }
	        unset($_dataQty);*/
	        $roster->quantities()->createMany($dataQty);
	        $roster->short_quantity = $qty;
	    }

	    // Данные из секции формы 7. Team Roster - старый код
        /*if(isset($request->team['size'])){
            $dataTeam = [];
            foreach($request->team['size'] as $key => $size){
                if($size != 'false'){
				    $dataTeam[$key] = [
					    'size'   => $size,
					    'number' => (isset($request->team['number'][$key]) ? $request->team['number'][$key] : ''),
					    'name'   => (isset($request->team['name'][$key]) ? $request->team['name'][$key] : '')
				    ];
			    }
            }

            $roster->teams()->createMany($dataTeam);
        }*/

        // Данные из секции формы 7. Team Roster - новый код
	    $dataTeam = [];
	    if(isset($request->team['size'])){
		    foreach($request->team['size'] as $key => $size){
			    $dataTeam[$key]['size'] = $size;
			    $dataTeam[$key]['number'] = '';
			    $dataTeam[$key]['name'] = '';
			    $dataTeam[$key]['note'] = '';
			    $dataTeam[$key]['shortsize'] = '';
		    }
	    }
	    if(isset($request->team['number'])){
		    foreach($request->team['number'] as $key => $size){
			    $dataTeam[$key]['number'] = $request->team['number'][$key];
			    if(!isset($dataTeam[$key]['size'])){
				    $dataTeam[$key]['size'] = '';
			    }
			    if(!isset($dataTeam[$key]['shortsize'])){
				    $dataTeam[$key]['shortsize'] = '';
			    }
			    $dataTeam[$key]['name'] = '';
			    $dataTeam[$key]['note'] = '';
		    }
	    }
	    if(isset($request->team['name'])){
		    foreach($request->team['name'] as $key => $size){
			    $dataTeam[$key]['name'] = $request->team['name'][$key];

			    if(!isset($dataTeam[$key]['size'])){
				    $dataTeam[$key]['size'] = '';
			    }
			    if(!isset($dataTeam[$key]['number'])){
				    $dataTeam[$key]['number'] = '';
			    }
			    if(!isset($dataTeam[$key]['shortsize'])){
				    $dataTeam[$key]['shortsize'] = '';
			    }
			    if(!isset($dataTeam[$key]['note'])){
				    $dataTeam[$key]['note'] = '';
			    }
		    }
	    }
	    if(isset($request->team['note'])){
		    foreach($request->team['note'] as $key => $size){
			    $dataTeam[$key]['note'] = $request->team['note'][$key];

			    if(!isset($dataTeam[$key]['size'])){
				    $dataTeam[$key]['size'] = '';
			    }
			    if(!isset($dataTeam[$key]['number'])){
				    $dataTeam[$key]['number'] = '';
			    }
			    if(!isset($dataTeam[$key]['shortsize'])){
				    $dataTeam[$key]['shortsize'] = '';
			    }
			    if(!isset($dataTeam[$key]['name'])){
				    $dataTeam[$key]['name'] = '';
			    }
		    }
	    }
	    if(isset($request->team['shortsize'])){
		    foreach($request->team['shortsize'] as $key => $size){
			    $dataTeam[$key]['shortsize'] = $request->team['shortsize'][$key];

			    if(!isset($dataTeam[$key]['size'])){
				    $dataTeam[$key]['size'] = '';
			    }
			    if(!isset($dataTeam[$key]['number'])){
				    $dataTeam[$key]['number'] = '';
			    }
			    if(!isset($dataTeam[$key]['name'])){
				    $dataTeam[$key]['name'] = '';
			    }
			    if(!isset($dataTeam[$key]['note'])){
				    $dataTeam[$key]['note'] = '';
			    }
		    }
	    }
	    if(!empty($dataTeam)){
	    	ksort($dataTeam);
	    	reset($dataTeam);

	    	// Сортировка по размеру
		    $_dataTeam = $dataTeam;
		    $dataTeam  = [];
	        foreach($ordered_sizes as $_size => $weight){
		        foreach($_dataTeam as $key => $_data){
				    if($_data['size'] == $_size){
				    	$_data['rowcolor'] = $colors_sizes[$_size];
					    $dataTeam[] = $_data;
					    unset($_dataTeam[$key]);
				    }
			        /*if(!isset($_data['rowcolor']) || $_data['rowcolor'] == ''){
				        $_data['rowcolor'] = '#eeeeee';
			        }*/
			    }
		    }
		    if(!empty($_dataTeam)){
			    $dataTeam += $_dataTeam;
		    }
		    unset($_dataTeam);
		    // end

		    $roster->teams()->createMany($dataTeam);
		    //Log::debug($dataTeam);
	    }


	    // Данные из секции формы 8. Attach Logo(s)
	    if($request->files->count() > 0){
           $data = arraysHelpers::saveFiles($request);
           $roster->files()->sync($data);
        }

	    if(!isset($request->environment)){
		    $request->environment = 'live';
	    }

	    /*$roster->admin_template = 'email.roster.admin';
	    $roster->client_template = 'email.roster.client';
	    if($request->environment == 'dev'){
		    $roster->admin_template = 'email.roster.preview.admin';
		    $roster->client_template = 'email.roster.preview.client';
	    }*/
		
		$roster->settings = roster::$default_settings;
		
		$data = [
        	'environment' => $request->environment,
            'roster' => $roster,
            'billing' => $billing,
            'jersey_detail' => json_decode($roster->jersey->colors)
        ];

        //Mail::to(config('mail.from.address'))->send(new RosterAdminMailable($data));
        //Mail::to($roster->client->email)->send(new RosterClientMailable($data));

	    Log::stack(['single'])->debug(json_encode($data));
	    $when = Carbon::now()->addSecond(30);

	    Log::stack(['custom'])->debug('Adding admin mail to the jobs table');

	    $mailable = new RosterAdminMailable($data);
	    $mailable->replyTo($roster->client->email, $roster->client->name);
		$mailable->subject('Roster Form #'.$roster->id);
	    Mail::to(config('mail.admin.to'))->later($when, $mailable);
	    unset($mailable);

	    if($request->environment == 'dev'){
		    #Mail::to('armen@digidez.com')->send(new RosterAdminDevMailable($data));
	    }else{

	    }

	    Log::stack(['custom'])->debug('Adding client mail to the task table');
	    $mailable = new RosterClientMailable($data);
		$mailable->replyTo(config('mail.client.reply'), config('mail.client.name'));
	    $mailable->subject('Teamco Roster Form #['.$roster->id.'] - ['.$roster->client->name.']');
	    Mail::to($roster->client->email)->later($when, $mailable);
	    unset($mailable);

	    $jobs = $this->get_jobs_count();
	    Log::stack(['custom'])->debug('Tasks in table: count('.$jobs['count'].'), ids('.$jobs['ids'].')');

	    Log::stack(['custom'])->debug('// END');

	    return response()->json(['data' => $roster, 'message' => 'success'], 200);
    }

	private function get_jobs_count(){
		$results = DB::table('jobs')->pluck('id');

		$count = $results->count();
		$ids = [];
		foreach($results as $result){
			$ids[] = $result;
		}

		return ['ids' => implode(', ', $ids), 'count' => $count];
	}

}
