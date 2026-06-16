<?php

namespace App\Http\Controllers\API;

use App\Color;
use App\country;
use App\Garment;
use App\Http\Controllers\Controller;
use App\Http\SVG\arrayUtilities;
use App\Http\SVG\lotSVGHelper;
use App\Http\SVG\Svg;
use App\Product;
use App\Settings;
use App\Size;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\ShipEngine\Rates;
use Illuminate\Support\Collection;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Storage;

class ServicesController extends Controller
{

    public function getColors(Request $request)
    {
        #Log::stack(['custom'])->debug($request->productid);

        $Colors = Color::orderBy('position')->get();
        $Product = Product::where('shopify_id', $request->productid)->first();

        if ($Product->colors) {
            $ProductColorSets = json_decode($Product->colors, true);
            $tmp_colors = [];
            foreach ($Colors as $k => $color) {
                if (in_array($color->value_code, $ProductColorSets)) {
                    $tmp_colors[] = $color;
                }
            }
            if (!empty($tmp_colors)) {
                $Colors = $tmp_colors;
            }
            unset($tmp_colors, $Product, $ProductColorSets);
        }

        return response()->json(['data' => $Colors], 200);
    }

    public function getProduct($id)
    {
        $Product = Product::where('shopify_id', $id)->first();
        if ($Product) {
            $data = [
                'name' => $Product->name,
                'builder_type' => $Product->builder_type,
                'shopify_id' => (string)$Product->shopify_id,
                'url_svg' => !empty($Product->url_svg) ? $Product->url_svg : null,
                'svg_info' => !empty($Product->svg_info) ? arrayUtilities::setInfo(json_decode($Product->svg_info, true)) : null,
                'dataExtra' => !empty($Product->svg_info) ? arrayUtilities::jsonData(json_decode($Product->svg_info, true)) : null,
            ];

            return response()->json(['data' => $data], 200);
        } else {
            return response()->json(['data' => 'not found'], 200);
        }
    }

    public function image(Request $request)
    {
        $product = Product::where('shopify_id', $request->productId)->first();
        $svgData = [];
        if ($product->svg_info != '') {
            $svgData = json_decode($product->svg_info, true);
        }
        $color = [];
        foreach ($request->customColor as $key => $value) {
            $color[$key] = $value;
            $svgData = LotSVGHelper::changeSvgClass($svgData, $key, $value);
        }
        $tempFile = 'svgTemp/' . $product->url_svg . '-' . md5(microtime(true));
        File::copy(public_path('jerseys/' . $product->url_svg . '.svg'), public_path('jerseys/' . $tempFile . '.svg'));
        # File::copy('public/jerseys/'.$product->url_svg.'.svg', 'public/jerseys/'.$tempFile.'.svg');
        # File::copy(base_path().'/public/jerseys/'.$product->url_svg.'.svg', base_path().'/public/jerseys/'.$tempFile.'.svg');
        if (Svg::updateStyleSVG($tempFile, $svgData)) {
            return response()->json(['data' => $tempFile]);
        }
    }

    public function getCountryStates()
    {
        return response()->json(['data' => Helper::getCountryStates()]);
    }

    public function getSizes()
    {
        return response()->json(['data' => Helper::getSizes()]);
    }

    public function getShippingRates(Request $request)
    {

        $admin_view = $request->post('admin_view');
        $country_code = $request->post('country_code');
        $state_province = $request->post('state_province');
        $postal_code = $request->post('postal_code');
        $units = $request->post('units');
        $jersey_type = $request->post('jersey_type');

        if (empty($country_code) || empty($state_province) || empty($postal_code) || empty($units)) {
            return response()->json([
                'raw' => [],
                'html' => '<tr><td colspan="4" class="text-center">ERROR!<br>Invalid form data.<br>All fields are required.</td></tr>',
                'desc' => '',
            ]);
        }

        $params = [
            "to_country_code" => strtoupper($country_code),
            "to_state_province" => strtoupper($state_province),
            "to_postal_code" => strtoupper($postal_code),
            "units" => intval($units),
            "jersey_type" => intval($jersey_type),
        ];

        $rates = new Rates();
        $rates->setAdminView($admin_view);
        $result = $rates->getEstimateRates($params);

        return response()->json($result);
    }

    public function getShippingFormFields()
    {

        $ship_engine_jersey_type_options = Settings::get('ship_engine_jersey_type_options');
        $ship_engine_province_codes = Settings::get('ship_engine_province_codes');

        if (!is_null($ship_engine_province_codes)) {
            $ship_engine_province_codes = explode(',', $ship_engine_province_codes);
        } else {
            $ship_engine_province_codes = [];
        }

        if (!is_null($ship_engine_jersey_type_options)) {
            $ship_engine_jersey_type_options = json_decode($ship_engine_jersey_type_options, true);
        } else {
            $ship_engine_jersey_type_options = [];
        }

        return response()->json(['html' => view('shipping.fields', [
            'ship_engine_jersey_type_options' => $ship_engine_jersey_type_options,
            'ship_engine_province_codes' => $ship_engine_province_codes,
        ])->render()]);
    }

    public function getShippingServices()
    {
        return response()->json(['data' => Helper::getShippingServices()]);
    }

    public function getRosterStaticFiles()
    {
        return response()->json(Helper::getRosterStaticFiles());
    }

    public function getQuoteRequestLabels()
    {
        $options = Settings::get('quote_request_labels');

        if (!empty($options))
            $options = json_decode($options, true);

        return response()->json($options);
    }

    public function getGarments()
    {
        $garments = Garment::orderBy('position')->get();

        if (!empty($garments)) {
            foreach ($garments as $garment) {
                if (!is_null($garment->main_image))
                    $garment->main_image = url(Storage::url($garment->main_image));
                if (!is_null($garment->size_image))
                    $garment->size_image = url(Storage::url($garment->size_image));
            }
        }

        return response()->json(['data' => $garments], 200);
    }
}

