<?php

namespace App\Http\Controllers\API;

use App\client;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\SVG\arraysHelpers;
use App\Mail\RosterAdminMailable;
use App\Mail\RosterClientMailable;
use App\MailLog;
use App\billing;
use App\roster;
use App\Size;
use App\jersey_detail;
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
    private $ordered_sizes = [];
    private $colors_sizes = [];

    public function create(Request $request)
    {
        //dd($request->all());

        if (empty($this->ordered_sizes))
            $this->setSizes();

        $requestRoster = $request->Roster;
        $requestRoster['guid'] = md5(time().rand(1000, 9999));

        //dd($requestRoster);

        $client = client::create($request->user);
        $billing = $client->billing()->create($request->billing);
        $roster = $client->roster()->create($requestRoster);

        #Log::stack(['custom'])->debug('Roster Form #'.$roster->id);

        // Данные из секции формы 3. Jersey Details
        $colors = [];
        if (isset($request->jerseyDetails['colors']))
            foreach ($request->jerseyDetails['colors'] as $k => $color)
                $colors[$k+1] = $color;

        $detail = $roster->jersey()->create([
            'style_code' => $request->jerseyDetails['style_code'],
            'colors' => json_encode($colors)
        ]);

        // Данные из секции формы 5. Jersey Quantities (Количество верхней одежды)
        $quantityData = $this->formatQuantities($request, 'top');
        if ($quantityData->qty) {
            $roster->quantities()->createMany($quantityData->dataQty);
            $roster->top_quantity = $quantityData->qty;
        }

        // Данные из секции формы 6. Shorts Quantities (Количество шортов)
        $quantityData = $this->formatQuantities($request, 'short');
        if ($quantityData->qty) {
            $roster->quantities()->createMany($quantityData->dataQty);
            $roster->short_quantity = $quantityData->qty;
        }

        // Данные из секции формы 7. Team Roster - новый код
        $dataTeam = $this->formatTeams($request);
        if (!empty($dataTeam)) {
            $roster->teams()->createMany($dataTeam);
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
            'files' => $roster->files,
            'billing' => $billing,
            'jersey_detail' => json_decode($roster->jersey->colors),
            //'edit_link' => sprintf($this->editUrl, $requestRoster['guid']),
        ];

        if ($request->environment == 'dev')
            $data['edit_link'] = sprintf($this->editUrl, $requestRoster['guid']);

        Log::stack(['single'])->debug(json_encode($data));

        $this->sendMail($data, $roster);

        return response()->json(['data' => $roster, 'message' => 'success', 'error' => 0], 200);
    }

    public function update(Request $request)
    {
        $guid = $request->guid;

        $roster = roster::where('guid', $guid)->first();
        //$client = client::find($roster->client_id);
        //$billing = billing::where('client_id', $roster->client_id);
        //$jersey = jersey_detail::where('roster_id', $roster->id);

		# Updating current Roster entry
		$roster->update($request->Roster);

		# Updating current Client entry
		//$client->update($request->user);
		$roster->client->update($request->user);

		# Updating current Client Billing entry
		//$billing->update($request->billing);
		$roster->client->billing->update($request->billing);

        // 3. Jersey Details
        $colors = [];
        if (isset($request->jerseyDetails['colors'])) {
            foreach ($request->jerseyDetails['colors'] as $k => $color) {
                $colors[$k + 1] = $color;
            }
        }
        //$jersey->update(['style_code' => $request->jerseyDetails['style_code'], 'colors' => json_encode($colors)]);
        $roster->jersey->update(['style_code' => $request->jerseyDetails['style_code'], 'colors' => json_encode($colors)]);

        // 5. Jersey Quantities + 6. Shorts Quantities
        $roster->quantities()->delete();

        // 5. Jersey Quantities
        $quantityData = $this->formatQuantities($request, 'top');
        if ($quantityData->qty) {
            $roster->quantities()->createMany($quantityData->dataQty);
            $roster->top_quantity = $quantityData->qty;
        }

        // 6. Shorts Quantities
        $quantityData = $this->formatQuantities($request, 'short');
        if ($quantityData->qty) {
            $roster->quantities()->createMany($quantityData->dataQty);
            $roster->short_quantity = $quantityData->qty;
        }

        // 7. Team Roster
        $roster->teams()->delete();

        $dataTeam = $this->formatTeams($request);
        if (!empty($dataTeam)) {
            $roster->teams()->createMany($dataTeam);
        }

		# Removing selected files
		if(isset($request->remove_file_roster)){
			foreach($request->remove_file_roster as $fid){
				$roster->files()->find($fid)->delete();
			}
		}

        // 8. Attach Logo(s)
        if ($request->files->count() > 0) {
            $data = arraysHelpers::saveFiles($request);
            $roster->files()->syncWithoutDetaching($data);
        }

        if (!isset($request->environment)) {
            $request->environment = 'live';
        }

        //$roster->settings = roster::$default_settings;

        $data = [
            'environment' => $request->environment,
            'roster' => $roster,
            'files' => $roster->files,
            //'billing' => $billing,
            'jersey_detail' => json_decode($roster->jersey->colors),
            //'edit_link' => sprintf($this->editUrl, $request->guid),
        ];

        if ($request->environment == 'dev')
            $data['edit_link'] = sprintf($this->editUrl, $request->guid);

        Log::stack(['single'])->debug(json_encode($data));

        $this->sendMail($data, $roster);

        return response()->json(['data' => $roster, 'message' => 'success', 'error' => 0], 200);
    }

    public function save(Request $request) {
        $this->debug = $request->environment == 'dev';

        //dd($request->all());

        $this->setSizes();

        if ($request->guid) {
            return $this->update($request);
        } else {
            return $this->create($request);
        }

        return response()->json(['data' => [], 'message' => 'error', 'error' => 1], 404);
    }

    private function setSizes()
    {
        $Sizes = Size::orderBy('weight')->get();

        foreach ($Sizes as $size) {
            $this->ordered_sizes[$size->name] = $size->weight;
            $this->colors_sizes[$size->name] = $size->color;
        }
    }

    private function formatQuantities($request, $type = 'top')
    {
        $dataQty = [];
        $qty = 0;

        switch ($type) {
            case "top":
                $requestData = $request->quantity;
                break;
            case "short":
                $requestData = $request->quantity_s;
                break;
        }

        if (isset($requestData['quantity'])) {
            foreach ($requestData['quantity'] as $key => $quantity) {
                if (!empty($quantity)) {
                    $dataQty[$key] = [
                        'quantity' => $quantity,
                        'size' => $requestData['size'][$key],
                        'type' => $type
                    ];
                    $qty += intval($quantity);
                }
            }
        }

        return (object) ['dataQty' => $dataQty, 'qty' => $qty];
    }

    private function formatTeams($request)
    {
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
            foreach ($this->ordered_sizes as $_size => $weight) {
                foreach ($_dataTeam as $key => $_data) {
                    if ($_data['size'] == $_size) {
                        $_data['rowcolor'] = $this->colors_sizes[$_size];
                        $dataTeam[] = $_data;
                        unset($_dataTeam[$key]);
                    }
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
        }

        return $dataTeam;
    }

    private function sendMail($data, $roster) {
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

        $response['data'] = [];

        if ($guid) {
            $roster = roster::where('guid', $guid)->first();
            $roster->settings = json_decode($roster->settings, true);
            $jersey = $roster->jersey;
            $jersey->colors = json_decode($jersey->colors, true);

            $response['data'] = [
                'roster' => $roster,
                'teams' => $roster->teams,
                'client' => $roster->client,
                'billing' => $roster->client->billing,
                'jersey' => $jersey,
                'quantities' => $roster->quantities,
                'files' => $roster->files,
            ];
        }

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
