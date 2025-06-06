<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'url_svg',
        'svg_info',
        'shopify_id',
        'colors',
        'color_autoupdate',
        'builder_type',
    ];
}

