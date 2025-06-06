<?php

namespace App\Http\Controllers;

use App\Color;
use App\Http\Requests\storeBuilder;
use App\Http\Shopify\Shopify;
use App\Http\SVG\arrayUtilities;
use App\Http\SVG\lotSVGHelper;
use App\Http\SVG\Svg;
use App\Product;
use App\roster;
use File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BuilderController extends Controller
{

    #public $shopify;

    public function __construct()
    {
        parent::__construct();
        #$this->shopify = new Shopify;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        #$Products = Product::paginate(10); #Commented by Armen

        #$Products = Product::all(); #Added by Armen
        $Products = [];

        return view('builder.index', ['Products' => $Products]);
    }

    public function parts(Request $request)
    {
        $sort_cols = [
            0 => 'products.id',
            1 => 'products.name',
        ];

        $query = Product::query();

        $query->select('*');

        if (isset($request->search)) {
            if (!empty($request->search['value'])) {

                $phrase = $request->search['value'];
                $like_phrase = "%" . $phrase . "%";
                $like_phrase_c = $phrase . "%";

                $colorsQuery = Color::query();
                $colorsQuery->where('name', 'like', $like_phrase_c);
                $colorsQuery->orWhere('value_code', 'like', $like_phrase);
                $colorsData = $colorsQuery->get()->pluck('value_code');

                $query->where('products.id', '=', $phrase);
                $query->orWhere('products.name', 'like', $like_phrase);

                if ($colorsData->count())
                    foreach ($colorsData as $valueCode)
                        $query->orWhere('products.svg_info', 'like', "%" . $valueCode . "%");
            }
        }


        if (isset($request->order)) {
            foreach ($request->order as $order) {
                $query->orderBy($sort_cols[$order['column']], $order['dir']);
            }
        }

        $query->offset($request->start);
        $query->limit($request->length);

        #dd($query->toSql());

        $data = $query->get();
        $total_count = $query->getQuery()->getCountForPagination();

        $roster = [];

        if ($data) {
            foreach ($data->all() as $item) {
                $checked = isset($item->color_autoupdate) && $item->color_autoupdate == 1 ? 'checked="checked"' : '';
                $roster[] = [
                    $item->id,
                    $item->name,
                    '<input type="checkbox" data-id="' . $item->id . '" name="color_autoupdate" ' . $checked . '>',
                    '<a href="' . route('builder.edit', $item->id) . '" class="btn btn-info text-light" title="Edit"><i class="fa fa-edit"></i></a>',
                    '<button class="btn btn-danger btn-remove" data-product-id="' . $item->id . '" data-product-name="' . $item->name . '" data-toggle="modal" data-target="#myModal" data-action="' . route('builder.destroy', $item->id) . '" title="Delete"><i class="fa fa-trash"></i></button>',
                ];
            }
        }

        $data = [
            'draw' => $request->draw,
            'recordsTotal' => $total_count,
            'recordsFiltered' => $total_count,
            'data' => $roster,
        ];

        #dd($roster);

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->updateShopifyProductsTable();

        return view('builder.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param storeBuilder $request
     * @return Response
     */
    public function store(storeBuilder $request)
    {

        $url = '/admin/products/' . $request->shopify_id . '.json';
        $getProduct = $this->shopify->get($url)->product;

        $cover = $request->file('uploadSVG');
        $nameImage = str_replace(" ", "-", $getProduct->title);
        $svg = $nameImage . '-' . time();
        $cover->move(public_path('jerseys'), $svg . '.svg');

        $request->merge(['name' => $getProduct->title, 'url_svg' => $svg]);

        $product = Product::create($request->all());

        return redirect('builder/' . $product->id)->with('status', 'Jersey created');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $infoSVG = Svg::GetDataFromSVG($product->url_svg);

        return view('builder.show', ['product' => $product, 'infoSVG' => $infoSVG]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $product = Product::findOrFail($id);

        $colors = Color::all()->sortBy('position');
        //$colors = Color::orderBy('position')->get();

        if (!empty($product->colors)) {
            $ProductColorSets = json_decode($product->colors, true);
        } else {
            $ProductColorSets = arrayUtilities::converToArrayKeys($colors);
        }

        $ProductSvgInfo = json_decode($product->svg_info, true);

        $jsonData = arrayUtilities::jsonData($ProductSvgInfo);

        $position = 1;
        if (isset($ProductSvgInfo['background']) && is_array($ProductSvgInfo['background'])) {
            $position += count($ProductSvgInfo['background']);
        }
        if (isset($ProductSvgInfo['colors'])) {
            $position += count($ProductSvgInfo['colors']);
        }

        return view('builder.edit', [
            'product' => $product,
            'variantColors' => $ProductSvgInfo,
            'colorSets' => $ProductColorSets,
            'jsonData' => $jsonData,
            'positions' => $position,
            'Colors' => arrayUtilities::converToArray($colors)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $svgChanges = [];
        $product = Product::find($id);

        if (isset($request->item['background'])) {
            $svgChanges['background'] = $request->item['background'];

        }
        if (isset($request->item['colors'])) {
            $svgChanges['colors'] = $request->item['colors'];
        }
        if (isset($request->item['lin_grad'])) {
            foreach ($request->item['lin_grad'] as $idLinGrad) {
                if (isset($request->item['linear_grad_color'][$idLinGrad])) {
                    $svgChanges['linearGradients'][] = $request->item['linear_grad_color'][$idLinGrad];
                }
            }
        }
        $svginfo = json_encode(Svg::setDataToNewSVG($id, $product->url_svg, $svgChanges));
        $request->merge(['svg_info' => $svginfo]);
        $product->find($id)->update($request->all());

        return redirect('builder/' . $id . '/edit')->with('status', 'Colour updated');
    }

    /**
     * Upload a new SVG file into the current product to edit
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateFileSVG(Request $request, $id)
    {
        $product = Product::find($id);
        File::Delete('jerseys/' . $product->url_svg . '.svg');
        $cover = $request->file('uploadSVG');
        $nameImage = str_replace(" ", "-", $product->name);
        $svg = $nameImage . '-' . time();
        $cover->move(public_path('jerseys'), $svg . '.svg');

        $product->find($id)->update([
            'url_svg' => $svg
        ]);
        return redirect('builder/' . $product->id)->with('status', 'Svg file updated');
    }

    /**
     * Update the specified colors in the product svg_info & image svg.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateColors(Request $request, $id)
    {
        #dd($request->all());

        $product = Product::find($id);

        $product->update(['color_autoupdate' => ($request->color_autoupdate == 'on' ? 1 : 0)]);

        if ($product->svg_info != '' && isset($request->colors) && is_array($request->colors)) {
            $ProductSvgInfo = json_decode($product->svg_info, true);
            foreach ($request->colors as $className => $color) {
                if (!preg_match("/^#[A-Za-z0-9]{3,6}$/i", $color)) {
                    continue;
                }
                $ProductSvgInfo = lotSVGHelper::changeSvgClass($ProductSvgInfo, $className, $color);
            }
        }
        $ProductSvgInfo['hide'] = [];
        if (isset($request->hide)) {
            foreach ($request->hide as $className => $valHide) {
                $ProductSvgInfo['hide'][] = $className;
            }
        }
        $ProductSvgInfo['sort'] = [];
        if (isset($request->sort)) {
            foreach ($request->sort as $className => $valSort) {
                $ProductSvgInfo['sort'][$className] = $valSort;
            }
        }
        $sort = $ProductSvgInfo['sort'];
        array_multisort($sort, SORT_ASC, $ProductSvgInfo['sort']);
        if (SVG::updateStyleSVG($product->url_svg, $ProductSvgInfo)) {
            $product->update([
                'svg_info' => json_encode($ProductSvgInfo)
            ]);
        }
        return redirect('builder/' . $id . '/edit')->with('status', 'Colour updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        File::Delete('jerseys/' . $product->url_svg . '.svg');
        $product->find($id)->delete();

        return redirect('builder')->with('status', 'Product Destroyed');
    }

    public function updateColorSets(Request $request, $id)
    {
        #Log::stack(['custom'])->debug($request->colorset);
        $product = Product::find($id);
        $product->update([
            'colors' => json_encode($request->colorset)
        ]);

        return redirect('builder/' . $id . '/edit')->with('status', 'Colour Sets updated');
    }

    public function shopify()
    {
        $sync_products = $this->getSyncShopifyProducts();

        return view('builder.shopify', ['SyncProducts' => $sync_products]);
    }

    public function ajaxUpdateFields(Request $request)
    {
        $return = ['error' => 1, 'message' => 'Error', 'id' => $request->id];

        if ($request->ajax()) {
            $_request = $request->post();
            unset($_request['_token']);

            if (Product::where('id', $request->id)->update($_request)) {
                $return['error'] = 0;
                $return['message'] = 'Update Succesfully';
            }
        }

        return response($return, 200);
    }

    public function getSyncShopifyProducts_OLD()
    {
        $limit = 100;
        $shopify_products = [];
        $sync_products = [];

        //$url = '/admin/products.json?page=1&limit=200&fields=id,title';
        //$url = '/admin/api/2020-04/product_listings.json?limit=200';

        $count_url = '/admin/products/count';
        $products_url = "/admin/products.json?page={page_num}&limit={$limit}&fields=id,title";

        $result = $this->shopify->get($count_url);
        #dd($result->count);

        $pages_count = ceil($result->count / $limit);
        #dd($pages_count);

        for ($i = 1; $i <= $pages_count; $i++) {
            $url = str_replace('{page_num}', $i, $products_url);
            $result = $this->shopify->get($url);
            foreach ($result->products as $product) {
                $shopify_products[$product->id] = $product->title;
            }
        }

        #dd($shopify_products);

        if (!empty($shopify_products)) {
            $shopify_ids = array_keys($shopify_products);
            $Products = DB::table('products')->whereIn('shopify_id', $shopify_ids)->get();

            #dd($Products->all());

            foreach ($Products->all() as $product) {
                if ($product->name != $shopify_products[$product->shopify_id]) {
                    $sync_products[] = [
                        'id' => $product->id,
                        'shopify_id' => $product->shopify_id,
                        'name' => $product->name,
                        'shopify_name' => $shopify_products[$product->shopify_id],
                    ];
                }
            }

            #dd($sync_products);
        }

        return $sync_products;
    }

    public function getSyncShopifyProducts()
    {
        $sync_products = [];

        $shopify_products = $this->getShopifyProducts();

        if (!empty($shopify_products)) {
            $shopify_ids = array_keys($shopify_products);
            $Products = DB::table('products')->whereIn('shopify_id', $shopify_ids)->get();

            foreach ($Products->all() as $product) {
                if ($product->name != $shopify_products[$product->shopify_id]) {
                    $sync_products[] = [
                        'id' => $product->id,
                        'shopify_id' => $product->shopify_id,
                        'name' => $product->name,
                        'shopify_name' => $shopify_products[$product->shopify_id],
                    ];
                }
            }
        }

        #dd($sync_products);

        return $sync_products;
    }

}
