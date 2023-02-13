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

class DesignClientMailable extends Mailable implements ShouldQueue {
    //use Queueable, SerializesModels;
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data){
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(){

	    $message = $this->markdown('email.design.client')
            ->from(config('mail.from.address'), config('mail.from_u.name'))
            ->subject('Teamco Custom Design Form #D'.$this->data['design']->id.' - '.$this->data['design']->client->name.'');

        if(count($this->data['design']->files) >0){
            foreach($this->data['design']->files as $file){
                $message->attach(public_path($file->url), [
                        'as' => $file->name
                    ]);
            }
        }
        return $message;
    }

	public function failed(){
		// Вызывается при ошибке в задаче...
		Log::stack(['custom'])->debug('Sending mail failed');

		$mailable = new NotifyMailable(['title' => 'The emails failed to send for Design Form #'.$this->data['design']->id.' to Client']);
		$mailable->subject('Teamco Admin Alert: Failed Email - Design #'.$this->data['design']->id);
		Mail::send($mailable);
	}

}
