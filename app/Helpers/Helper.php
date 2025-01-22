<?php

namespace App\Helpers;

use App\country;
use App\Settings;
use App\Size;

class Helper
{
    public static function getCountryStates()
    {
        $Countries = Country::all();
        $data = [];

        foreach ($Countries as $key => $country) {
            $data[] = [
                'name' => ucfirst($country->name),
                'states' => $country->states,
            ];
        }

        return $data;
    }

    public static function getSizes()
    {
        return Size::orderBy('weight')
            ->where(['private' => 0])
            ->get();
    }

    public static function getShippingServices()
    {
        $collection = collect();

        $ship_engine_services_options = Settings::get('ship_engine_services_options');
        $ship_engine_services_options = json_decode($ship_engine_services_options, true);

        foreach ($ship_engine_services_options as $k => $option)
            if ($option['status'] == 1)
                $collection->push([
                    'id' => $k,
                    'name' => (!is_null($option['desc'])) ? sprintf('%s - %s', $option['desc'], $option['type']) : $option['type']
                ]);

        $data = $collection->sortBy('name');

        $data->prepend(["id" => 1, "name" => "Pickup (Markham, ON)"]);

        /*$data = collect([
            //["id" => 0, "name" => "No Preference - Teamco will choose"],
            ["id" => 1, "name" => "Pickup (Markham, ON)"],
            ["id" => 2, "name" => "Canada Post - Expedited Parcel"],
            ["id" => 3, "name" => "Canada Post - Xpresspost"],
            ["id" => 4, "name" => "Canada Post - Priority"],
            ["id" => 5, "name" => "UPS Standard"],
            ["id" => 6, "name" => "UPS Express Early"],
            ["id" => 7, "name" => "UPS Express"],
            ["id" => 8, "name" => "Purolator Ground"],
            ["id" => 9, "name" => "Purolator Express"],
        ]);*/

        return $data;
    }

    public static function getRosterStaticFiles()
    {
        $res = [];
        $roster_form_files_options = Settings::get('roster_form_files_options');

        if (!empty($roster_form_files_options)) {
            $roster_form_files_options = json_decode($roster_form_files_options, true);
            foreach ($roster_form_files_options as $option) {
                $res[$option['id']] = [
                    'url' => url('storage/' . $option['file']),
                    'display' => (isset($option['display']) ? $option['display'] : 0),
                ];
            }
        }

        return $res;
    }


}
