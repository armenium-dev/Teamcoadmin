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

class RosterClientMailable extends Mailable implements ShouldQueue
{
    //use Queueable, SerializesModels;
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(){

	    /*$template = 'email.roster.client';
	    if(isset($this->data['environment'])){
		    if($this->data['environment'] == 'dev'){
			    $template = 'email.roster.preview.client';
		    }
	    }*/

	    $message = $this->markdown('email.roster.client')
                    ->from(config('mail.from.address'), config('mail.from_u.name'))
                    //->cc($this->data['roster']->client->email, $this->data['roster']->client->name)
                    //->bcc($this->data['roster']->client->email, $this->data['roster']->client->name)
                    //->replyTo(config('mail.from.address'), config('mail.from.name'))
                    //->subject('Roster Form #'.$this->data['roster']->id);
                    ->subject('Teamco Roster Form #['.$this->data['roster']->id.'] - ['.$this->data['roster']->client->name.']');

        if(count($this->data['roster']->files) >0){
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
		Log::stack(['custom'])->debug('Sending mail failed');

		$mailable = new NotifyMailable(['title' => 'The emails failed to send for Roster Form #'.$this->data['roster']->id.' to Client']);
		$mailable->subject('Teamco Admin Alert: Failed Email - Roster #'.$this->data['roster']->id);
		Mail::send($mailable);
	}

}
