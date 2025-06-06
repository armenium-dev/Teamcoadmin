<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Shopify\Shopify;
use App\Product;
use Illuminate\Http\Request;
use App\Settings;
use Symfony\Component\VarDumper\VarDumper;
use App\ProductShopify;
use Illuminate\Support\Facades\DB;

class InfoController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all Products of shopify.
     */
    public function getProducts(Request $request)
    {
        $phrase = $request->get('phrase');
        $title = '%' . $phrase . '%';

        $products = ProductShopify::where('title', 'LIKE', $title)->get(['shopify_id AS id', 'title']);

        return $products ? response()->json($products) : [];
    }

    public function getProducts_OLD(Request $request)
    {
        $phrase = $request->get('phrase');
        $title = str_replace(' ', '+', $phrase);
        #VarDumper::dump($title);
        $url = '/admin/products.json?limit=250&title=' . $title;

        $getProducts = $this->shopify->get($url)->products;
        #dd($getProducts);

        return $getProducts;
    }

    /**
     * Check if product selected has a SVG file associated
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function checkAvailability($id)
    {
        $product = Product::where('shopify_id', $id)->select('url_svg')->first();

        if (isset($product->url_svg) && $product->url_svg != '') {
            return response()->json(['message' => 'not']);
        } else {
            return response()->json(['message' => 'yes']);
        }
    }


}
