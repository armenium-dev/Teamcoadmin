<?php

namespace App\Http\Controllers\API;

use App\client;
use App\Http\Controllers\Controller;
use App\Http\Shopify\Shopify;
use App\Http\SVG\arraysHelpers;
use App\Mail\AdminMailable;
use App\Mail\ClientMailable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\MailLog;

class QuotesController extends Controller
{
    public $shopify;
    private $logging = true;

    public function __construct()
    {
        $this->shopify = new Shopify;
    }

    public function create(Request $request)
    {
        $client = client::create($request->user);
        $quote = $client->quote()->create($request->quote);

        if ($request->files->count() > 0) {
            $data = arraysHelpers::saveFiles($request);
            $quote->files()->sync($data);
        }

        if (isset($request->products)) {
            if (count($request->products) > 0) {
                $data = arraysHelpers::saveProducts($request);
                $quote->styles()->createMany($data);
            }
        }

        $url = '/admin/customers/search.json?query=' . $quote->client->email;
        $verifyUser = (count($this->shopify->get($url)->customers) > 0) ? 'Yes' : 'No';

        $products = arraysHelpers::returnProducts($quote->styles);
        $mail_data = [
            'quote' => $quote,
            'customerShopify' => $verifyUser,
            'products' => $products,
            'subject' => 'Teamco Web Inquiry - [' . $quote->client->name . '] - #' . $quote->id,
        ];
        //Mail::to(config('mail.from.address'))->send(new AdminMailable($mail_data));
        //Mail::to($quote->client->email)->send(new ClientMailable($mail_data));

        // Begin Test
        //$mailable = new ClientMailable($mail_data);
        //$mailable->subject('Teamco Web Inquiry - ['.$quote->client->name.']');
        //Mail::to('armen@digidez.com')->send($mailable);
        // End Test

        $when = Carbon::now()->addSecond(30);

        $mailable = new AdminMailable($mail_data);
        #$mailable->from(config('mail.admin.from'), config('mail.admin.name'));
        $mailable->replyTo($quote->client->email, $quote->client->name);
        $mailable->subject('Teamco Web Inquiry #' . $quote->id);
        $job_id = Mail::to(config('mail.admin.to'))->later($when, $mailable);
        unset($mailable);
        MailLog::create(['object_id' => $quote->id, 'body' => 'Teamco Web Inquiry for Admin', 'controller' => __CLASS__, 'job_id' => $job_id]);

        $mailable = new ClientMailable($mail_data);
        #$mailable->from(config('mail.client.from'), config('mail.client.name'));
        $mailable->replyTo(config('mail.client.reply'), config('mail.client.name'));
        $mailable->subject('Teamco Web Inquiry - [' . $quote->client->name . '] - #' . $quote->id);
        $job_id = Mail::to($quote->client->email)->later($when, $mailable);
        unset($mailable);
        MailLog::create(['object_id' => $quote->id, 'body' => 'Teamco Web Inquiry for Client', 'controller' => __CLASS__, 'job_id' => $job_id]);

        /*if($this->logging){
            $jobs = $this->get_jobs_count();
            Log::stack(['custom'])->debug('// BEGIN _________________________________________');
            Log::stack(['custom'])->debug(__CLASS__);
            Log::stack(['custom'])->debug('Teamco Web Inquiry #'.$quote->id);
            Log::stack(['custom'])->debug('Adding admin mail to the task table');
            Log::stack(['custom'])->debug('Adding client mail to the task table');
            Log::stack(['custom'])->debug('Tasks in table: count('.$jobs['count'].'), ids('.$jobs['ids'].')');
            Log::stack(['custom'])->debug('// END');
        }*/

        return response()->json(['data' => $quote, 'message' => 'success'], 200);
    }

    private function get_jobs_count()
    {
        $results = DB::table('jobs')->pluck('id');

        $count = $results->count();
        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result;
        }

        return ['ids' => implode(', ', $ids), 'count' => $count];
    }

}
