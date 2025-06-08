<?php

namespace App\Http\SVG;

use App\file;

class arraysHelpers
{
    /**
     * This is a simple function to resolve some
     * params in the info about the data of SVG
     *
     * @param array $array
     * @param string $param
     * @param string $a
     *
     * @return \Illuminate\Http\Response
     */
    public static function fillArray($array, $param, $a)
    {
        return (isset($array[$param]) ? $array[$param] : $a);
    }

    /**
     * This function set a format to colors
     *
     * @param array $colors
     */
    public static function converToArray($colors)
    {
        $data = [];
        foreach ($colors as $key => $color) {
            $data[$color->value_code] = $color->name;
        }

        return $data;
    }

    public static function saveFiles($request)
    {
        foreach ($request->file('files') as $key => $image) {
            $name = $image->getClientOriginalName();
            $image->move(public_path() . '/images/', $name);
            $dataInsert[$key] = ['name' => $name, 'url' => '/images/' . $name];
            $files[$key] = file::create($dataInsert[$key]);
            $data[] = $files[$key]->id;
        }

        return $data;
    }

    public static function saveProducts($request)
    {
        foreach ($request->products as $key => $product) {
            /*foreach ($product['data'] as $k => $v) {
                if ($v['label'] == 'Garment Type') {
                    unset($product['data'][$k]);
                }
            }*/

            $dataInsert[$key] = [
                'product_name' => $product['id'],
                'product_shopify_id' => $product['product_id'],
                'style_info' => json_encode($product['data']),
                'quantity' => (isset($request->Quantity[$key]) && !empty($request->Quantity[$key]) ? $request->Quantity[$key] : 1),
                'url_svg_temp' => (isset($product['url_svg'])) ? $product['url_svg'] : ''
            ];
        }

        return $dataInsert;
    }

    public static function returnProducts($styles)
    {
        $data = [];
        foreach ($styles as $key => $style) {
            $style_info = json_decode($style->style_info);

            $data [] = [
                'quantity' => $style->quantity,
                'data' => $style_info,
                'url_svg_temp' => $style->url_svg_temp
            ];
        }

        return $data;
    }
}
