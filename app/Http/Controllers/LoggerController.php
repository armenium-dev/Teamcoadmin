<?php

namespace App\Http\Controllers;


use App\FailedJob;
use App\Job;
use App\MailLog;
use App\Settings;
use Illuminate\Http\Request;

class LoggerController extends Controller{

	public $shopify;
	private $is_sent_status = [
		0 => 'Not checked yet',
		1 => 'Error',
		2 => 'No',
		3 => 'Yes',
	];

	public function __construct(){
		$this->middleware('auth');
	}

	/**
	 * Display a listing of the resource.
	 * @return \Illuminate\Http\Response
	 */
	public function index(){
		$logs_count = MailLog::all()->count();

		return view('logger.index', ['logs_count' => $logs_count]);
	}

	public function parts(Request $request){
		$sort_cols = [
			0 => 'id',
			1 => 'body',
			2 => 'sent',
			3 => 'controller',
			4 => 'object_id',
			5 => 'job_id',
			6 => 'fail_id',
			7 => 'updated_at',
		];
		$query = MailLog::query();

		$query->select('*');

		if(isset($request->search)){
			if(!empty($request->search['value'])){

				$phrase = trim($request->search['value']);
				$like_phrase = "%".$phrase."%";
				$sent_phrase = '';

				$s = strtolower($phrase);

				foreach($this->is_sent_status as $k => $v){
					if(str_contains(strtolower($v), $s)){
						$sent_phrase = $k;
					}
				}

				#$query->where('id', '=', $phrase);
				if(!empty($sent_phrase)){
					$query->orWhere('sent', '=', $sent_phrase);
				}
				$query->orWhere('object_id', '=', $phrase);
				$query->orWhere('job_id', '=', $phrase);
				$query->orWhere('controller', 'like', $like_phrase);
				$query->orWhere('body', 'like', $like_phrase);
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

		$data = $query->get();
		$total_count = $query->getQuery()->getCountForPagination();

		$logs = [];

		if($data){
			foreach($data->all() as $log){
				$controller = explode("\\", $log->controller);

				$logs[] = [
					$log->id,
					$log->body,
					$this->is_sent_status[$log->sent],
					str_replace('Controller', '', end($controller)),
					$log->object_id,
					$log->job_id,
					$log->fail_id,
					$log->updated_at->format('M d, Y - H:i'),
					#$log->sent > 0 ? '<i class="fa fa-check"></i>': '<button class="js_check_status btn btn-info py-0 px-4" data-reference_id="'.$log->id.'" data-action="'.route('logger.check', $log->id).'" title="Check sent status"><i class="fa fa-check"></i></button>',
				];
			}
		}

		$data = [
			'draw' => $request->draw,
			'recordsTotal' => $total_count,
			'recordsFiltered' => $total_count,
			'data' => $logs,
		];

		#dd($logs);

		return response()->json($data, 200);
	}

	public function checkOne(Request $request){

	}

	public function checkAll(){
		$res = ['checked' => 0, 'changed' => 0];

		#$logs = MailLog::where(['sent' => 0])->get();
		$logs = MailLog::whereIn('sent', [0, 2])->get();

		if($logs->count()){
			foreach($logs as $log){
				$sent_status = $this->checkByLog($log);

				if($sent_status != $log->sent){
					$log->sent = $sent_status;
					$log->save();
					$res['changed']++;
				}

				$res['checked']++;
			}
		}

		return $res;
	}

	private function checkByLog(MailLog $log){
		$sent_status = 2;

		$job = Job::find($log->job_id);

		if(is_null($job)){
			$sent_status = 3;

			$failed_job = FailedJob::where('payload', 'like', '%s:2:"id";i:'.$log->object_id.';%')->first();

			if(!is_null($failed_job)){
				$sent_status = 1;
				$log->fail_id = $failed_job->id;
				$log->save();
			}
		}

		return $sent_status;
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show($id){
		$model = MailLog::findOrFail($id);

		return view('logger.show', ['model' => $model]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id){
		MailLog::find($id)->delete();

		return redirect('logger')->with('status', 'Log Destroyed');
	}

	/**------------------- OLD METHODS ---------------------------**/
	public function index_old(){

		$logs = $custom_logs = $worker_logs = [];

		if(file_exists(storage_path('logs/custom.log'))){
			$custom_logs = file(storage_path('logs/custom.log'));
		}
		if(file_exists(storage_path('logs/worker.log'))){
			//$worker_logs = file(storage_path('logs/worker.log'));
		}

		$logs = $this->format_logs($custom_logs, $worker_logs);

		return view('logger.index', ['logs' => $logs]);
	}

	public function format_logs($custom_logs, $worker_logs){
		$ret = [];
		$j = 0;
		if(!empty($custom_logs)){
			foreach($custom_logs as $k => $log){
				$phrase = '';
				if(strstr($log, 'local.DEBUG:') !== false){
					$phrase = 'local.DEBUG:';
				}elseif(strstr($log, 'production.DEBUG:') !== false){
					$phrase = 'production.DEBUG:';
				}
				
				if(!empty($phrase)){
					$a = explode($phrase, $log);
					$ret[$j]['time']    = trim($a[0]);
					$ret[$j]['content'] = trim($a[1]);
				}
				
				if(!empty($worker_logs)){
					preg_match_all('/(ids(.*))/', $log, $output_array);

					if(!empty($output_array[2])){
						$s   = str_replace(array('(', ')', ' '), '', $output_array[2][0]);
						$ids = explode(',', $s);
						foreach($ids as $id){
							foreach($worker_logs as $wlog){
								if(strstr($wlog, '['.$id.']') !== false){
									$j++;
									$a                    = explode('['.$id.']', $wlog);
									$ret[($j)]['time']    = trim($a[0]);
									$ret[($j)]['content'] = '['.$id.'] '.trim($a[1]);
								}
							}
						}
					}
				}
				$j++;
			}
		}

		return $ret;
	}

}
