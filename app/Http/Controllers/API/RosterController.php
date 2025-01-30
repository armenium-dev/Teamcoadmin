<?php

namespace App\Http\Controllers\API;

use App\client;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\SVG\arraysHelpers;
use App\Mail\RosterAdminMailable;
use App\Mail\RosterClientMailable;
use App\MailLog;
use App\quanity;
use App\roster;
use App\Size;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RosterController extends Controller
{

    private $editUrl = 'https://teamcosportswear.com/pages/roster-form?guid=%s';
    private $debug = false;

    public function create(Request $request)
    {
        //dd($request->all());

        $ordered_sizes = [];
        $colors_sizes = [];
        $Sizes = Size::orderBy('weight')->get();
        foreach ($Sizes as $size) {
            $ordered_sizes[$size->name] = $size->weight;
            $colors_sizes[$size->name] = $size->color;
        }
        unset($Sizes, $size);

        $requestRoster = $request->Roster;
        $requestRoster['guid'] = (string) Str::uuid();

        //dd($requestRoster);

        $client = client::create($request->user);
        $billing = $client->billing()->create($request->billing);
        $roster = $client->roster()->create($requestRoster);

        #Log::stack(['custom'])->debug('Roster Form #'.$roster->id);

        // Данные из секции формы 3. Jersey Details
        $detail = $roster->jersey()->create([
            'style_code' => $request->jerseyDetails['style_code'],
            'colors' => (isset($request->jerseyDetails['colors'])) ? json_encode($request->jerseyDetails['colors']) : ''
        ]);

        // Данные из секции формы 5. Jersey Quantities (Количество верхней одежды)
        if (isset($request->quantity['quantity'])) {
            $dataQty = [];
            $qty = 0;
            foreach ($request->quantity['quantity'] as $key => $quantity) {
                if (!empty($quantity)) {
                    $dataQty[$key] = [
                        'quantity' => $quantity,
                        'size' => $request->quantity['size'][$key],
                        'type' => 'top'
                    ];
                    $qty += intval($quantity);
                }
            }
            $roster->quantities()->createMany($dataQty);
            $roster->top_quantity = $qty;
        }

        // Данные из секции формы 6. Shorts Quantities (Количество шортов)
        if (isset($request->quantity_s['quantity_s'])) {
            $dataQty = [];
            $qty = 0;
            foreach ($request->quantity_s['quantity_s'] as $key => $quantity) {
                if (!empty($quantity)) {
                    $dataQty[$key] = [
                        'quantity' => $quantity,
                        'size' => $request->quantity_s['size_s'][$key],
                        'type' => 'short'
                    ];
                    $qty += intval($quantity);
                }
            }
            $roster->quantities()->createMany($dataQty);
            $roster->short_quantity = $qty;
        }

        // Данные из секции формы 7. Team Roster - новый код
        $dataTeam = [];
        if (isset($request->team['size'])) {
            foreach ($request->team['size'] as $key => $size) {
                $dataTeam[$key]['size'] = $size;
                $dataTeam[$key]['number'] = '';
                $dataTeam[$key]['name'] = '';
                $dataTeam[$key]['note'] = '';
                $dataTeam[$key]['shortsize'] = '';
            }
        }
        if (isset($request->team['number'])) {
            foreach ($request->team['number'] as $key => $size) {
                $dataTeam[$key]['number'] = $request->team['number'][$key];
                if (!isset($dataTeam[$key]['size'])) {
                    $dataTeam[$key]['size'] = '';
                }
                if (!isset($dataTeam[$key]['shortsize'])) {
                    $dataTeam[$key]['shortsize'] = '';
                }
                $dataTeam[$key]['name'] = '';
                $dataTeam[$key]['note'] = '';
            }
        }
        if (isset($request->team['name'])) {
            foreach ($request->team['name'] as $key => $size) {
                $dataTeam[$key]['name'] = $request->team['name'][$key];

                if (!isset($dataTeam[$key]['size'])) {
                    $dataTeam[$key]['size'] = '';
                }
                if (!isset($dataTeam[$key]['number'])) {
                    $dataTeam[$key]['number'] = '';
                }
                if (!isset($dataTeam[$key]['shortsize'])) {
                    $dataTeam[$key]['shortsize'] = '';
                }
                if (!isset($dataTeam[$key]['note'])) {
                    $dataTeam[$key]['note'] = '';
                }
            }
        }
        if (isset($request->team['note'])) {
            foreach ($request->team['note'] as $key => $size) {
                $dataTeam[$key]['note'] = $request->team['note'][$key];

                if (!isset($dataTeam[$key]['size'])) {
                    $dataTeam[$key]['size'] = '';
                }
                if (!isset($dataTeam[$key]['number'])) {
                    $dataTeam[$key]['number'] = '';
                }
                if (!isset($dataTeam[$key]['shortsize'])) {
                    $dataTeam[$key]['shortsize'] = '';
                }
                if (!isset($dataTeam[$key]['name'])) {
                    $dataTeam[$key]['name'] = '';
                }
            }
        }
        if (isset($request->team['shortsize'])) {
            foreach ($request->team['shortsize'] as $key => $size) {
                $dataTeam[$key]['shortsize'] = $request->team['shortsize'][$key];

                if (!isset($dataTeam[$key]['size'])) {
                    $dataTeam[$key]['size'] = '';
                }
                if (!isset($dataTeam[$key]['number'])) {
                    $dataTeam[$key]['number'] = '';
                }
                if (!isset($dataTeam[$key]['name'])) {
                    $dataTeam[$key]['name'] = '';
                }
                if (!isset($dataTeam[$key]['note'])) {
                    $dataTeam[$key]['note'] = '';
                }
            }
        }
        if (!empty($dataTeam)) {
            ksort($dataTeam);
            reset($dataTeam);

            // Сортировка по размеру
            $_dataTeam = $dataTeam;
            $dataTeam = [];
            foreach ($ordered_sizes as $_size => $weight) {
                foreach ($_dataTeam as $key => $_data) {
                    if ($_data['size'] == $_size) {
                        $_data['rowcolor'] = $colors_sizes[$_size];
                        $dataTeam[] = $_data;
                        unset($_dataTeam[$key]);
                    }
                    /*if(!isset($_data['rowcolor']) || $_data['rowcolor'] == ''){
                        $_data['rowcolor'] = '#eeeeee';
                    }*/
                }
            }
            if (!empty($_dataTeam)) {
                $dataTeam += $_dataTeam;
            }
            unset($_dataTeam);

            foreach ($dataTeam as $k => $v)
                if ($v['size'] == 'false' && empty($v['number']) && empty($v['name']) && empty($v['note']) && $v['shortsize'] == 'false')
                    unset($dataTeam[$k]);
            // end

            $roster->teams()->createMany($dataTeam);
            //Log::debug($dataTeam);
        }

        // Данные из секции формы 8. Attach Logo(s)
        if ($request->files->count() > 0) {
            $data = arraysHelpers::saveFiles($request);
            $roster->files()->sync($data);
        }

        if (!isset($request->environment)) {
            $request->environment = 'live';
        }

        $roster->settings = roster::$default_settings;

        $data = [
            'environment' => $request->environment,
            'roster' => $roster,
            'billing' => $billing,
            'jersey_detail' => json_decode($roster->jersey->colors),
            'edit_link' => sprintf($this->editUrl, $requestRoster['guid']),
        ];

        //Mail::to(config('mail.from.address'))->send(new RosterAdminMailable($data));
        //Mail::to($roster->client->email)->send(new RosterClientMailable($data));

        Log::stack(['single'])->debug(json_encode($data));

        if (!$this->debug) {
            $when = Carbon::now()->addSecond(30);

            $mailable = new RosterAdminMailable($data);
            $mailable->replyTo($roster->client->email, $roster->client->name);
            $mailable->subject('Roster Form #' . $roster->id);
            $job_id = Mail::to(config('mail.admin.to'))->later($when, $mailable);
            unset($mailable);
            MailLog::create(['object_id' => $roster->id, 'body' => 'Roster Form for Admin', 'controller' => __CLASS__, 'job_id' => $job_id]);

            $mailable = new RosterClientMailable($data);
            $mailable->replyTo(config('mail.client.reply'), config('mail.client.name'));
            $mailable->subject('Teamco Roster Form #[' . $roster->id . '] - [' . $roster->client->name . ']');
            $job_id = Mail::to($roster->client->email)->later($when, $mailable);
            unset($mailable);
            MailLog::create(['object_id' => $roster->id, 'body' => 'Roster Form for Client', 'controller' => __CLASS__, 'job_id' => $job_id]);
        } else {
            #Mail::to('armen@digidez.com')->send(new RosterAdminDevMailable($data));
        }

        #$jobs = $this->get_jobs_count();
        #Log::stack(['custom'])->debug('Tasks in table: count('.$jobs['count'].'), ids('.$jobs['ids'].')');

        #Log::stack(['custom'])->debug('// END');

        return response()->json(['data' => $roster, 'message' => 'success'], 200);
    }

    public function update(Request $request)
    {
        //dd(json_encode($request->all()));
        if ($this->debug) {
            dd([
                $request->Roster['comments'],
                #$request->user,
                #$request->designDetails,
                #$request->files,
            ]);

        }

        //Log::stack(['single'])->debug(var_export($_POST, true));
        //return response()->json(['data'=>$_POST, 'message' => 'success' ],200);

        #Log::stack(['custom'])->debug('// BEGIN _________________________________________');
        #Log::stack(['custom'])->debug(__CLASS__);

        $ordered_sizes = [];
        $colors_sizes = [];
        $Sizes = Size::orderBy('weight')->get();
        foreach ($Sizes as $size) {
            $ordered_sizes[$size->name] = $size->weight;
            $colors_sizes[$size->name] = $size->color;
        }
        unset($Sizes, $size);

        $request->Roster->guid = (string) Str::uuid();

        $client = client::update($request->user);
        $billing = $client->billing()->update($request->billing);
        $roster = $client->roster()->update($request->Roster);

        //$roster->guid = (string) Str::uuid();
        //$roster->update();

        #Log::stack(['custom'])->debug('Roster Form #'.$roster->id);

        // Данные из секции формы 3. Jersey Details
        $detail = $roster->jersey()->update([
            'style_code' => $request->jerseyDetails['style_code'],
            'colors' => (isset($request->jerseyDetails['colors'])) ? json_encode($request->jerseyDetails['colors']) : ''
        ]);

        // Данные из секции формы 5. Jersey Quantities (Количество верхней одежды)
        if (isset($request->quantity['quantity'])) {
            $dataQty = [];
            $qty = 0;
            foreach ($request->quantity['quantity'] as $key => $quantity) {
                $dataQty[$key] = [
                    'quantity' => $quantity,
                    'size' => $request->quantity['size'][$key],
                    'type' => 'top'
                ];
                $qty += intval($quantity);
            }
            /*$_dataQty = $dataQty;
            $dataQty = [];
            foreach($ordered_sizes as $_size => $weight){
                foreach($_dataQty as $key => $_data){
                    if($_data['size'] == $_size){
                        $dataQty[] = $_data;
                    }
                }
            }
            unset($_dataQty);*/
            $roster->quantities()->updateMany($dataQty);
            $roster->top_quantity = $qty;
        }

        // Данные из секции формы 6. Shorts Quantities (Количество шортов)
        if (isset($request->quantity_s['quantity_s'])) {
            $dataQty = [];
            $qty = 0;
            foreach ($request->quantity_s['quantity_s'] as $key => $quantity) {
                $dataQty[$key] = [
                    'quantity' => $quantity,
                    'size' => $request->quantity_s['size_s'][$key],
                    'type' => 'short'
                ];
                $qty += intval($quantity);
            }
            /*$_dataQty = $dataQty;
            $dataQty = [];
            foreach($ordered_sizes as $_size => $weight){
                foreach($_dataQty as $key => $_data){
                    if($_data['size'] == $_size){
                        $dataQty[] = $_data;
                    }
                }
            }
            unset($_dataQty);*/
            $roster->quantities()->updateMany($dataQty);
            $roster->short_quantity = $qty;
        }

        // Данные из секции формы 7. Team Roster - старый код
        /*if(isset($request->team['size'])){
            $dataTeam = [];
            foreach($request->team['size'] as $key => $size){
                if($size != 'false'){
				    $dataTeam[$key] = [
					    'size'   => $size,
					    'number' => (isset($request->team['number'][$key]) ? $request->team['number'][$key] : ''),
					    'name'   => (isset($request->team['name'][$key]) ? $request->team['name'][$key] : '')
				    ];
			    }
            }

            $roster->teams()->createMany($dataTeam);
        }*/

        // Данные из секции формы 7. Team Roster - новый код
        $dataTeam = [];
        if (isset($request->team['size'])) {
            foreach ($request->team['size'] as $key => $size) {
                $dataTeam[$key]['size'] = $size;
                $dataTeam[$key]['number'] = '';
                $dataTeam[$key]['name'] = '';
                $dataTeam[$key]['note'] = '';
                $dataTeam[$key]['shortsize'] = '';
            }
        }
        if (isset($request->team['number'])) {
            foreach ($request->team['number'] as $key => $size) {
                $dataTeam[$key]['number'] = $request->team['number'][$key];
                if (!isset($dataTeam[$key]['size'])) {
                    $dataTeam[$key]['size'] = '';
                }
                if (!isset($dataTeam[$key]['shortsize'])) {
                    $dataTeam[$key]['shortsize'] = '';
                }
                $dataTeam[$key]['name'] = '';
                $dataTeam[$key]['note'] = '';
            }
        }
        if (isset($request->team['name'])) {
            foreach ($request->team['name'] as $key => $size) {
                $dataTeam[$key]['name'] = $request->team['name'][$key];

                if (!isset($dataTeam[$key]['size'])) {
                    $dataTeam[$key]['size'] = '';
                }
                if (!isset($dataTeam[$key]['number'])) {
                    $dataTeam[$key]['number'] = '';
                }
                if (!isset($dataTeam[$key]['shortsize'])) {
                    $dataTeam[$key]['shortsize'] = '';
                }
                if (!isset($dataTeam[$key]['note'])) {
                    $dataTeam[$key]['note'] = '';
                }
            }
        }
        if (isset($request->team['note'])) {
            foreach ($request->team['note'] as $key => $size) {
                $dataTeam[$key]['note'] = $request->team['note'][$key];

                if (!isset($dataTeam[$key]['size'])) {
                    $dataTeam[$key]['size'] = '';
                }
                if (!isset($dataTeam[$key]['number'])) {
                    $dataTeam[$key]['number'] = '';
                }
                if (!isset($dataTeam[$key]['shortsize'])) {
                    $dataTeam[$key]['shortsize'] = '';
                }
                if (!isset($dataTeam[$key]['name'])) {
                    $dataTeam[$key]['name'] = '';
                }
            }
        }
        if (isset($request->team['shortsize'])) {
            foreach ($request->team['shortsize'] as $key => $size) {
                $dataTeam[$key]['shortsize'] = $request->team['shortsize'][$key];

                if (!isset($dataTeam[$key]['size'])) {
                    $dataTeam[$key]['size'] = '';
                }
                if (!isset($dataTeam[$key]['number'])) {
                    $dataTeam[$key]['number'] = '';
                }
                if (!isset($dataTeam[$key]['name'])) {
                    $dataTeam[$key]['name'] = '';
                }
                if (!isset($dataTeam[$key]['note'])) {
                    $dataTeam[$key]['note'] = '';
                }
            }
        }
        if (!empty($dataTeam)) {
            ksort($dataTeam);
            reset($dataTeam);

            // Сортировка по размеру
            $_dataTeam = $dataTeam;
            $dataTeam = [];
            foreach ($ordered_sizes as $_size => $weight) {
                foreach ($_dataTeam as $key => $_data) {
                    if ($_data['size'] == $_size) {
                        $_data['rowcolor'] = $colors_sizes[$_size];
                        $dataTeam[] = $_data;
                        unset($_dataTeam[$key]);
                    }
                    /*if(!isset($_data['rowcolor']) || $_data['rowcolor'] == ''){
                        $_data['rowcolor'] = '#eeeeee';
                    }*/
                }
            }
            if (!empty($_dataTeam)) {
                $dataTeam += $_dataTeam;
            }
            unset($_dataTeam);
            // end

            $roster->teams()->createMany($dataTeam);
            //Log::debug($dataTeam);
        }


        // Данные из секции формы 8. Attach Logo(s)
        if ($request->files->count() > 0) {
            $data = arraysHelpers::saveFiles($request);
            $roster->files()->sync($data);
        }

        if (!isset($request->environment)) {
            $request->environment = 'live';
        }

        /*$roster->admin_template = 'email.roster.admin';
        $roster->client_template = 'email.roster.client';
        if($request->environment == 'dev'){
            $roster->admin_template = 'email.roster.preview.admin';
            $roster->client_template = 'email.roster.preview.client';
        }*/

        $roster->settings = roster::$default_settings;

        $data = [
            'environment' => $request->environment,
            'roster' => $roster,
            'billing' => $billing,
            'jersey_detail' => json_decode($roster->jersey->colors)
        ];

        //Mail::to(config('mail.from.address'))->send(new RosterAdminMailable($data));
        //Mail::to($roster->client->email)->send(new RosterClientMailable($data));

        Log::stack(['single'])->debug(json_encode($data));
        $when = Carbon::now()->addSecond(30);

        #Log::stack(['custom'])->debug('Adding admin mail to the jobs table');

        $mailable = new RosterAdminMailable($data);
        $mailable->replyTo($roster->client->email, $roster->client->name);
        $mailable->subject('Roster Form #' . $roster->id);
        $job_id = Mail::to(config('mail.admin.to'))->later($when, $mailable);
        unset($mailable);
        MailLog::create(['object_id' => $roster->id, 'body' => 'Roster Form for Admin', 'controller' => __CLASS__, 'job_id' => $job_id]);

        if ($request->environment == 'dev') {
            #Mail::to('armen@digidez.com')->send(new RosterAdminDevMailable($data));
        } else {

        }

        #Log::stack(['custom'])->debug('Adding client mail to the task table');
        $mailable = new RosterClientMailable($data);
        $mailable->replyTo(config('mail.client.reply'), config('mail.client.name'));
        $mailable->subject('Teamco Roster Form #[' . $roster->id . '] - [' . $roster->client->name . ']');
        $job_id = Mail::to($roster->client->email)->later($when, $mailable);
        unset($mailable);
        MailLog::create(['object_id' => $roster->id, 'body' => 'Roster Form for Client', 'controller' => __CLASS__, 'job_id' => $job_id]);

        #$jobs = $this->get_jobs_count();
        #Log::stack(['custom'])->debug('Tasks in table: count('.$jobs['count'].'), ids('.$jobs['ids'].')');

        #Log::stack(['custom'])->debug('// END');

        return response()->json(['data' => $roster, 'message' => 'success'], 200);
    }

    public function save(Request $request) {
        $this->debug = $request->environment == 'dev';

        //dd($request->all());

        if ($request->guid) {
            return $this->update($request);
        } else {
            return $this->create($request);
        }

        return response()->json(['data' => [], 'message' => 'error'], 404);
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

    public function page()
    {
        $response = ['success' => true];

        $countryStates = Helper::getCountryStates();
        $sizes = Helper::getSizes();
        $shippingServices = Helper::getShippingServices();
        $rosterStaticFiles = Helper::getRosterStaticFiles();

        $sizesHtml = [];
        foreach ($sizes as $size)
            $sizesHtml[] = sprintf('<option value="%s">%s</option>', $size['name'], $size['name']);
        $sizesHtml = implode('', $sizesHtml);

        $response['html'] = view('rosters.shopify.page',
            compact('countryStates', 'sizes', 'shippingServices', 'rosterStaticFiles', 'sizesHtml')
        )->render();

        return response()->json($response);
    }

    public function getPageData(Request $request)
    {
        $response = ['success' => true];

        $guid = $request->guid;

        $response['guid'] = $guid;
        $response['countryStates'] = Helper::getCountryStates();
        $response['sizes'] = Helper::getSizes();
        $response['shippingServices'] = Helper::getShippingServices();
        $response['rosterStaticFiles'] = Helper::getRosterStaticFiles();

        $sizesHtml = [];
        foreach ($response['sizes'] as $size)
            $sizesHtml[] = sprintf('<option value="%s">%s</option>', $size['name'], $size['name']);
        $response['sizesHtml'] = implode('', $sizesHtml);

        return response()->json($response);
    }

    public function verifyRecaptcha(Request $request)
    {
        $secret = 'ВАШ_SECRET_KEY';
        $response = $request->post('g-recaptcha-response');
        $remoteIp = $_SERVER['REMOTE_ADDR'];

        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}&remoteip={$remoteIp}");
        $captchaSuccess = json_decode($verify);

        $success = $captchaSuccess->success ? true : false;

        return response()->json(['success' => $success, 'message' => 'success'], 200);
    }

}
