<?php


namespace App\Console\Commands;

use App\Http\Controllers\LoggerController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckMailSendStatus extends Command{

	/**
	 * The name and signature of the console command.
	 * @var string
	 */
	protected $signature = 'checkmailsentstatus:run';

	/**
	 * The console command description.
	 * @var string
	 */
	protected $description = 'Check Mail Sent Status';

	private $controller;

	/**
	 * Create a new command instance.
	 * @return void
	 */
	public function __construct(LoggerController $C){
		parent::__construct();

		$this->controller = $C;
	}

	/**
	 * Execute the console command.
	 * @return mixed
	 */
	public function handle(){
		Log::stack(['cron'])->debug(__CLASS__.'::'.__FUNCTION__.' - RUN');

		$result = $this->controller->checkAll();

		#Log::stack(['cron'])->debug('---------- Begin '.$this->description.' ----------');
		#Log::stack(['cron'])->debug(sprintf('Checked: %d; Changed: %d', $result['checked'], $result['changed']));
		#Log::stack(['cron'])->debug('---------- End '.$this->description.' ----------');
	}

}