<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RosterAdminMailable extends Mailable implements ShouldQueue{
	//use Queueable, SerializesModels;
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	public $data;

	/**
	 * Create a new message instance.
	 * @return void
	 */
	public function __construct($data){
		$this->data = $data;
	}

	/**
	 * Build the message.
	 * @return $this
	 */
	public function build(){

		//Log::debug(var_export($this->data, true));
		//Log::stack(['single'])->debug(var_export($this->data, true));

		/*$template = 'email.roster.admin';
		if(isset($this->data['environment'])){
			if($this->data['environment'] == 'dev'){
				$template = 'email.roster.preview.admin';
			}
		}*/

		/*if($this->data['roster']->client->email == 'armen@digidez.com'){
			$this->data['roster']->client->email = 'armendigidez.com';
		}*/

		$message = $this->markdown('email.roster.admin')
			->from(config('mail.admin.from'), config('mail.admin.name'))
			->replyTo($this->data['roster']->client->email, $this->data['roster']->client->name)
			->subject('Roster Form #'.$this->data['roster']->id);


		if(count($this->data['roster']->files) > 0){
			foreach($this->data['roster']->files as $file){
				$message->attach(public_path($file->url), [
					'as' => $file->name
				]);
			}
		}

		return $message;
	}

	public function failed(){
		// Вызывается при ошибке в задаче...
		Log::stack(['custom'])->debug('Roster (admin) - Sending mail failed');

		$mailable = new NotifyMailable(['title' => 'The emails failed to send for Roster Form #'.$this->data['roster']->id.' to Admin']);
		$mailable->subject('Teamco Admin Alert: Failed Email - Roster #'.$this->data['roster']->id);
		Mail::send($mailable);
	}

}
