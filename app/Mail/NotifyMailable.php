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

class NotifyMailable extends Mailable {

	use SerializesModels;

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
		$message = $this
			->markdown('email.notify.template')
			->from(config('mail.notify.email'), config('mail.from.name'));

		return $message;
	}

	public function failed(){
		// Вызывается при ошибке в задаче...
		Log::stack(['custom'])->debug('Notify mail failed');
	}
}
