<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\TokenProduct;

class TokenController extends Controller
{
    //
    public function index($token)
    {
        $Tokens = TokenProduct::where('token', $token)->get();

        return response()->json(['data' => json_decode($Tokens)], 200);
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
