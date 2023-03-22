<?php

namespace App\Http\Controllers\API;

use App\client;
use App\Http\Controllers\Controller;
use App\Http\SVG\arraysHelpers;
use App\Mail\DesignAdminMailable;
use App\Mail\DesignClientMailable;
use App\MailLog;
use App\quanity;
use App\Size;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DesignController extends Controller {
	
	private $debug = false;
	private $logging = false;
	
    public function create(Request $request){
    	
    	if($this->debug){
		    dd([
			    $request->user,
			    $request->designDetails,
			    $request->files,
		    ]);
	    }
	    
    	$client = client::create($request->user);
    	$design = $client->design()->create($request->designDetails);
	
	    if($request->files->count() > 0){
           $data = arraysHelpers::saveFiles($request);
           $design->files()->sync($data);
        }

	    if(!isset($request->environment)){
		    $request->environment = 'live';
	    }
	    
        $data = [
        	'environment' => $request->environment,
            'design' => $design,
        ];

        //Mail::to(config('mail.from.address'))->send(new RosterAdminMailable($data));
        //Mail::to($design->client->email)->send(new RosterClientMailable($data));
	
	    $when = Carbon::now()->addSecond(30);

	    $mailable = new DesignAdminMailable($data);
	    $mailable->replyTo($design->client->email, $design->client->name);
	    $mailable->subject('Custom Design Form #D'.$design->id);
		$job_id = Mail::to(config('mail.admin.to'))->later($when, $mailable);
	    unset($mailable);
		MailLog::create(['object_id' => $design->id, 'body' => 'Design Form for Admin', 'controller' => __CLASS__, 'job_id' => $job_id]);

	    if($request->environment == 'dev'){
		    #Mail::to('armen@digidez.com')->send(new DesignAdminMailable($data));
	    }else{

	    }
	
	    $mailable = new DesignClientMailable($data);
	    $mailable->replyTo(config('mail.client.reply'), config('mail.client.name'));
	    $mailable->subject('Teamco Custom Design Form #D'.$design->id.' - '.$design->client->name.'');
		$job_id = Mail::to($design->client->email)->later($when, $mailable);
	    unset($mailable);
		MailLog::create(['object_id' => $design->id, 'body' => 'Design Form for Admin', 'controller' => __CLASS__, 'job_id' => $job_id]);

	    /*if($this->logging){
		    $jobs = $this->get_jobs_count();
		    Log::stack(['custom'])->debug('// BEGIN _________________________________________');
		    Log::stack(['custom'])->debug(__CLASS__);
		    Log::stack(['custom'])->debug('Custom Design Form #'.$design->id);
		    Log::stack(['single'])->debug(json_encode($data));
		    Log::stack(['custom'])->debug('Adding admin mail to the task table');
		    Log::stack(['custom'])->debug('Adding client mail to the task table');
		    Log::stack(['custom'])->debug('Tasks in table: count('.$jobs['count'].'), ids('.$jobs['ids'].')');
		    Log::stack(['custom'])->debug('// END');
	    }*/

	    return response()->json(['data' => $design, 'message' => 'success'], 200);
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
