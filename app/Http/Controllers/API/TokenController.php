<?php

namespace App\Http\Controllers\API;

use App\Garment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\TokenProduct;

class TokenController extends Controller
{
    public function index($token)
    {
        $tokens = TokenProduct::where('token', $token)->get();

        if ($tokens) {
            foreach ($tokens as $token) {
                if (!is_null($token->data)) {
                    $data = json_decode($token->data, true);

                    if (isset($data['colors'])) {
                        foreach ($data['colors'] as $k => $v){
                            $data['colors'][$k]['label'] = "Color ".($k + 1);
                        }
                    } else $data['colors'] = [];

                    if (isset($data['garment_type'])) {
                        //$key = count($data['colors']);

                        $garments = Garment::whereIn('id', $data['garment_type'])->get();

                        if ($garments) {
                            foreach ($garments as $garment) {
                                $data['colors'][] = [
                                    "label" => "Garment Type",
                                    "code" => $garment->code,
                                    "name" => $garment->code . ' - ' . $garment->title,
                                ];
                            }
                        }
                    }

                    if (isset($data['artisan_theme_code'])) {
                        $data['colors'][] = [
                            "label" => "Artisan Theme Code",
                            "code" => '',
                            "name" => $data['artisan_theme_code'],
                        ];
                    }

                    $token->data = json_encode($data);
                }
            }
        }

        return response()->json(['data' => $tokens], 200);
    }

    public function create()
    {
        return response()->json(['data' => Str::random(40)], 200);
    }

    public function storeProduct(Request $request)
    {
        $request->merge(['data' => json_encode($request->data)]);

        $Token = TokenProduct::create($request->all());

        return response()->json(['data' => $Token], 200);
    }

    public function DeleteProduct($id)
    {
        $Token = TokenProduct::find($id)->delete();
        return response()->json(['data' => $Token], 200);
    }

    public function DeleteProductToken($id)
    {
        $Token = TokenProduct::where('token', $id)->delete();
        return response()->json(['data' => $Token], 200);
    }

}
