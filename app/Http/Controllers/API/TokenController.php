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
                    }
                    if (isset($data['garment_type'])) {
                        $key = count($data['colors']);
                        $garment = Garment::find($data['garment_type']);
                        $data['colors'][$key] = [
                            "label" => "Garment Type",
                            "code" => $garment->code,
                            "name" => $garment->code.' - '.$garment->title,
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
