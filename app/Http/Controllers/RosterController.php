<?php

namespace App\Http\Controllers;

use App\billing;
use App\country;
use App\Http\SVG\arraysHelpers;
use App\Mail\RosterAdminMailable;
use App\Mail\RosterClientMailable;
use App\quantity;
use App\quote;
use App\roster;
use App\client;
use App\Size;
use App\jersey_detail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RosterController extends Controller{
	
	private $default_settings = [
		'section_1'  => ['title' => '1. Contact and Shipping Information'],
		'section_2'  => ['title' => '2. Shipping Method'],
		'section_3'  => ['title' => '3. Jersey Details'],
		'section_4'  => ['title' => '4. Accessory Items'],
		'section_5'  => ['title' => '5. Number Colors'],
		'section_6'  => ['title' => '6. Artwork Placement and Order Description'],
		'section_7'  => ['title' => '7. Jersey Quantities'],
		'section_8'  => ['title' => '8. Shorts or Socks Quantities'],
		'section_9'  => ['title' => '9. Team Roster', 'table_head' => [
			'head_1' => '-',
			'head_2' => 'Jersey Size',
			'head_3' => 'Jersey #',
			'head_4' => 'Jersey Name',
			'head_5' => 'Notes',
			'head_6' => 'Shorts Size',
		]],
		'section_10'  => ['title' => '10. Attach Logo(s)'],
		'section_11' => ['title' => '11. Resend email to:'],
	];
	
	private $shipping_services = [
		["id" => 0, "name" => "No Preference - Teamco will choose"],
		["id" => 1, "name" => "Pickup (Markham, ON)"],
		["id" => 2, "name" => "Canada Post - Expedited Parcel"],
		["id" => 3, "name" => "Canada Post - Xpresspost"],
		["id" => 4, "name" => "Canada Post - Priority"],
		["id" => 5, "name" => "UPS Standard"],
		["id" => 6, "name" => "UPS Express Early"],
		["id" => 7, "name" => "UPS Express"],
		["id" => 8, "name" => "Purolator Ground"],
		["id" => 9, "name" => "Purolator Express"],
	];
	
	/**
	 * Display a listing of the resource.
	 * @return \Illuminate\Http\Response
	 */
	public function index(){
		#$rosters = roster::with('client')->get();
		$rosters = [];
		
		return view('rosters.index', ['rosters' => $rosters]);
	}
	
	public function parts(Request $request){
		$sort_cols = [
			0 => 'rosters.id',
			1 => 'clients.name',
			2 => 'clients.company',
			3 => 'rosters.created_at',
		];
		#dd($request);
		
		$query = roster::query();
		
		#$query->select('roster.*, clients.name, clients.company, styles.quantity');
		$query->select('rosters.*');
		$query->leftJoin('clients', 'clients.id', '=', 'rosters.client_id');
		
		if(isset($request->search)){
			if(!empty($request->search['value'])){
				
				$phrase      = $request->search['value'];
				$like_phrase = "%".$phrase."%";
				
				$query->where('rosters.id', '=', $phrase);
				$query->orWhere('clients.name', 'like', $like_phrase);
				$query->orWhere('clients.company', 'like', $like_phrase);
			}
		}
		
		if(isset($request->order)){
			foreach($request->order as $order){
				$query->orderBy($sort_cols[$order['column']], $order['dir']);
			}
		}
		
		$query->offset($request->start);
		$query->limit($request->length);
		
		#dd($query->toSql());
		
		$data        = $query->get();
		$total_count = $query->getQuery()->getCountForPagination();
		
		$roster = [];
		
		if($data){
			foreach($data->all() as $item){
				$roster[] = [
					$item->id,
					$item->client->name,
					$item->client->company,
					$item->created_at->format('M d, Y'),
					'<a href="'.route('roster.show', $item->id).'" class="btn btn-primary">View Details</a>',
					'<a href="'.route('roster.edit', $item->id).'" class="btn btn-secondary">Edit</a>',
					'<button class="btn btn-danger btn-remove" data-reference_id="'.$item->id.'" data-toggle="modal" data-target="#myModal" data-action="'.route('roster.destroy', $item->id).'" title="Delete"><i class="fa fa-trash"></i></button>',
				];
			}
		}
		
		$data = [
			'draw'            => $request->draw,
			'recordsTotal'    => $total_count,
			'recordsFiltered' => $total_count,
			'data'            => $roster,
		];
		
		#dd($roster);
		
		return response()->json($data, 200);
	}
	
	/**
	 * Show the form for creating a new resource.
	 * @return \Illuminate\Http\Response
	 */
	public function create(){}
	
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request){}
	
	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show($id){
		$roster = roster::findOrFail($id);
		
		$colors_sizes = [];
		$Sizes        = Size::orderBy('weight')->get();
		foreach($Sizes as $size){
			$colors_sizes[$size->name] = $size->color;
		}
		unset($Sizes, $size);
		
		
		foreach($roster->teams as $id => $team){
			if($team->rowcolor == '' && $team->size != "false" && $team->size != ''){
				$roster->teams[$id]->rowcolor = $colors_sizes[$team->size];
			}
		}
		
		//dd($roster->teams);
		
		$tops = $shorts = [];
		
		foreach($roster->quantities as $quantity){
			if($quantity->type == 'top'){
				$tops[] = $quantity;
			}elseif($quantity->type == 'short'){
				$shorts[] = $quantity;
			}
		}
		
		$roster->tops   = $tops;
		$roster->shorts = $shorts;
		
		if(!empty($roster->settings)){
			$roster->settings = json_decode($roster->settings, true);
			if(count($roster->settings) < count(roster::$default_settings)){
				$settings = [];
				$settings['section_1'] = roster::$default_settings['section_1'];
				$settings['section_2'] = roster::$default_settings['section_2'];
				$settings['section_3'] = roster::$default_settings['section_3'];
				$settings['section_4'] = roster::$default_settings['section_4'];
				$settings['section_5'] = roster::$default_settings['section_5'];
				$settings['section_6'] = roster::$default_settings['section_6'];
				$settings['section_7'] = roster::$default_settings['section_7'];
				$settings['section_8'] = roster::$default_settings['section_8'];
				$settings['section_9'] = $roster->settings['section_8'];
				$settings['section_10'] = roster::$default_settings['section_10'];
				$settings['section_11'] = roster::$default_settings['section_11'];
				$roster->settings = $settings;
			}
			$roster->settings = array_replace_recursive(roster::$default_settings, $roster->settings);
			#dd($roster->settings);
		}else{
			$roster->settings = roster::$default_settings;
		}
		
		$data = [
			'roster'        => $roster,
			'colors_sizes'  => $colors_sizes,
			'jersey_detail' => json_decode($roster->jersey->colors)
		];
		
		return view('rosters.show', $data);
	}
	
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id){
		$roster = roster::findOrFail($id);
		
		$Countries = Country::all();
		$states    = [];
		foreach($Countries as $key => $country){
			$states[] = [
				'name'   => ucfirst($country->name),
				'states' => $country->states,
			];
		}
		
		$colors_sizes = [];
		$Sizes        = Size::orderBy('weight')->get();
		foreach($Sizes as $size){
			$colors_sizes[$size->name] = $size->color;
		}
		unset($Sizes, $size);
		
		#dd($roster->teams);
		foreach($roster->teams as $id => $team){
			if($team->rowcolor == '' && $team->size != "false" && !empty($team->size)){
				$roster->teams[$id]->rowcolor = $colors_sizes[$team->size];
			}
		}
		
		#dd($Sizes);
		#dd($roster->teams);
		
		$tops = $shorts = [];
		foreach($colors_sizes as $size => $v){
			$tops[$size]   = '';
			$shorts[$size] = '';
		}
		
		foreach($roster->quantities as $quantity){
			$count = is_null($quantity->quantity) ? 0 : $quantity->quantity;
			if($quantity->type == 'top'){
				$tops[$quantity->size] = $count;
			}elseif($quantity->type == 'short'){
				$shorts[$quantity->size] = $count;
			}
		}
		
		#dd($shorts);
		
		$roster->tops   = $tops;
		$roster->shorts = $shorts;
		
		$jersey_detail = [];
		$a             = ["1" => '', "2" => '', "3" => '', "4" => '', "5" => ''];
		$j             = json_decode($roster->jersey->colors, true);
		foreach($a as $k => $v){
			if(isset($j[$k])){
				$jersey_detail[$k] = $j[$k];
			}else{
				$jersey_detail[$k] = $v;
			}
		}
		
		if(!empty($roster->files)){
			foreach($roster->files as $k => $file){
				$roster->files[$k]->url = $this->replace_unknow_file_url($file);
			}
		}
		
		if(!empty($roster->settings)){
			$roster->settings = json_decode($roster->settings, true);
			if(count($roster->settings) < count(roster::$default_settings)){
				$settings = [];
				$settings['section_1'] = roster::$default_settings['section_1'];
				$settings['section_2'] = roster::$default_settings['section_2'];
				$settings['section_3'] = roster::$default_settings['section_3'];
				$settings['section_4'] = roster::$default_settings['section_4'];
				$settings['section_5'] = roster::$default_settings['section_5'];
				$settings['section_6'] = roster::$default_settings['section_6'];
				$settings['section_7'] = roster::$default_settings['section_7'];
				$settings['section_8'] = roster::$default_settings['section_8'];
				$settings['section_9'] = $roster->settings['section_8'];
				$settings['section_10'] = roster::$default_settings['section_10'];
				$settings['section_11'] = roster::$default_settings['section_11'];
				$roster->settings = $settings;
			}
			$roster->settings = array_replace_recursive(roster::$default_settings, $roster->settings);
			#dd($roster->settings);
		}else{
			$roster->settings = roster::$default_settings;
		}
		
		$data = [
			'roster'            => $roster,
			'shipping_services' => $this->shipping_services,
			'colors_sizes'      => $colors_sizes,
			'states'            => $states,
			'jersey_detail'     => $jersey_detail,
			'teams_empty_rows'  => 40 - count($roster->teams),
		];
		
		#dd($roster->files);
		
		return view('rosters.edit', $data);
	}
	
	/**
	 * Update the specified resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id){
		#dd($request->toArray());
		
		# Getting Roster model by ID
		$roster = roster::find($request->roster['id']);
		
		# Creating ordered sizes & colors
		$ordered_sizes = [];
		$colors_sizes  = [];
		$Sizes         = Size::orderBy('weight')->get();
		foreach($Sizes as $size){
			$ordered_sizes[$size->name] = $size->weight;
			$colors_sizes[$size->name]  = $size->color;
		}
		unset($Sizes, $size);
		
		$new_roster = $request->roster;
		
		$new_roster['settings'] = array_merge(roster::$default_settings, $new_roster['settings']);
		#dd($new_roster['settings']);
		$new_roster['settings'] = json_encode($new_roster['settings']);
		
		# Updating current Roster entry
		roster::find($request->roster['id'])->update($new_roster);
		
		# Updating current Client entry
		client::find($request->client['id'])->update($request->client);
		
		# Updating current Client Billing entry
		billing::where(['client_id' => $request->client['id']])->update($request->billing);

		# Updating jersey_detail entry
		$jersey_detail           = $request->jersey_detail;
		$jersey_detail['colors'] = json_encode($jersey_detail['colors']);
		jersey_detail::find($request->jersey_detail['id'])->update($jersey_detail);
		
		# Replacing Quantity entries
		$dataQty = [];
		$qty     = ['top' => 0, 'short' => 0];
		foreach($request->quantity as $type => $values){
			foreach($values as $size => $quantity){
				if(!is_null($quantity)){
					$dataQty[]  = ['quantity' => $quantity, 'size' => $size, 'type' => $type];
					$qty[$type] += intval($quantity);
				}
			}
		}
		$roster->top_quantity   = $qty['top'];
		$roster->short_quantity = $qty['short'];
		$roster->quantities()->delete();
		$roster->quantities()->createMany($dataQty);
		
		# Replacing Teams entries
		$dataTeam = [];
		foreach($request->team as $team_id => $team){
			if($team['size'] != "false" || !is_null($team['number']) || !is_null($team['name']) || !is_null($team['note']) || $team['shortsize'] != "false"){
				$dataTeam[] = $team;
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
					if($_data['size'] == 'false' || $_data['size'] === false){
						$_data['size']           = null;
						$_dataTeam[$key]['size'] = $_data['size'];
					}
					if($_data['shortsize'] == 'false' || $_data['shortsize'] === false){
						$_data['shortsize']           = null;
						$_dataTeam[$key]['shortsize'] = $_data['shortsize'];
					}
					
					if($_data['size'] == $_size){
						$_data['rowcolor'] = $colors_sizes[$_size];
						$dataTeam[]        = $_data;
						unset($_dataTeam[$key]);
					}
				}
			}
			if(!empty($_dataTeam)){
				$dataTeam += $_dataTeam;
			}
			unset($_dataTeam);
			
			$roster->teams()->delete();
			$roster->teams()->createMany($dataTeam);
		}
		
		# Removing selected files
		if(isset($request->remove_file_roster)){
			foreach($request->remove_file_roster as $fid){
				$roster->files()->find($fid)->delete();
			}
		}
		
		# Adding new files
		if($request->files->count() > 0){
			$data = arraysHelpers::saveFiles($request);
			$roster->files()->syncWithoutDetaching($data);
		}
		
		# Resending mails
		if(isset($request->send_email)){
			$roster = roster::find($request->roster['id']);
			$roster->settings = json_decode($roster->settings, true);
			#dd($roster->settings);
			
			$data = [
				'environment'   => $request->environment,
				'roster'        => $roster,
				'jersey_detail' => json_decode($roster->jersey->colors)
			];
			foreach($request->send_email as $type){
				$this->send_mail_to($type, $data);
			}
		}
		
		
		return redirect('roster/'.$request->roster['id'])->with('status', 'Roster updated');
	}
	
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id){
		$model = roster::find($id);
		$model->find($id)->delete();
		
		return redirect('roster')->with('status', 'Roster Destroyed');
	}
	
	public function replace_unknow_file_url($file){
		$allow_formats = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
		$pathinfo      = pathinfo($file->url);
		
		if(!isset($pathinfo['extension'])){
			$pathinfo['extension'] = 'raw';
		}
		
		if(!in_array($pathinfo['extension'], $allow_formats)){
			$file->url = url('/images/file-formats/'.$pathinfo['extension'].'.svg');
		}
		
		return $file->url;
	}
	
	public function send_mail_to($type, $data){
		$when = Carbon::now()->addSecond(30);
		
		Log::stack(['custom'])->debug('Adding '.$type.' mail to the task table');
		
		switch($type){
			case "admin":
				$mailable = new RosterAdminMailable($data);
				$mailable->replyTo($data['roster']->client->email, $data['roster']->client->name);
				Mail::to(config('mail.from.address'))->later($when, $mailable);
				unset($mailable);
				break;
			case "client":
				$mailable = new RosterClientMailable($data);
				$mailable->subject('Teamco Roster Form #['.$data['roster']->id.'] - ['.$data['roster']->client->name.']');
				Mail::to($data['roster']->client->email)->later($when, $mailable);
				unset($mailable);
				break;
		}
		
		$jobs = $this->get_jobs_count();
		Log::stack(['custom'])->debug('Tasks in table: count('.$jobs['count'].'), ids('.$jobs['ids'].')');
		
		Log::stack(['custom'])->debug('// END');
	}
	
	private function get_jobs_count(){
		$results = DB::table('jobs')->pluck('id');
		
		$count = $results->count();
		$ids   = [];
		foreach($results as $result){
			$ids[] = $result;
		}
		
		return ['ids' => implode(', ', $ids), 'count' => $count];
	}


}

