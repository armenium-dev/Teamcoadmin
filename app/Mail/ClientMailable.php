<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClientMailable extends Mailable implements ShouldQueue {
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
	
	/*public function handle(JobProcessing $event)
	{
		$this->delete();
	}*/
	
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(){
	    
        $message = $this->markdown('email.quotes.client')
                        ->from(config('mail.from.address'), config('mail.from_u.name'))
	                    //->from($this->data['quote']->client->email, $this->data['quote']->client->name)
			            //->cc($this->data['quote']->client->email, $this->data['quote']->client->name)
			            //->bcc($this->data['quote']->client->email, $this->data['quote']->client->name)
			            //->replyTo($this->data['quote']->client->email, $this->data['quote']->client->name)
			            //->replyTo(config('mail.from.address'), config('mail.from.name'))
			            ->replyTo(config('mail.from.address'), config('mail.from_u.name'))
				        //->subject($this->data['subject']);
			            ->subject('Teamco Web Inquiry - '.$this->data['quote']->client->name.' - #'.$this->data['quote']->id);

	    if(count($this->data['quote']->files) > 0){
		    #Log::stack(['custom'])->debug('Attached files: '.$this->data['quote']->files);
		    foreach($this->data['quote']->files as $file){
			    $message->attach(public_path($file->url), ['as' => $file->name]);
		    }
	    }
	    if(count($this->data['products']) > 0){
		    foreach($this->data['products'] as $product){
			    $message->attach($product['url_svg_temp']);
		    }
	    }


        return $message;

    }

	public function failed(){
		// Вызывается при ошибке в задаче...
		Log::stack(['custom'])->debug('Quote (client) - Sending mail failed');

		$mailable = new NotifyMailable(['title' => 'The emails failed to send for Inquiry Form #'.$this->data['quote']->id.' to Client']);
		$mailable->subject('Teamco Admin Alert: Failed Email - Inquiry #'.$this->data['quote']->id);
		Mail::send($mailable);
	}

}
