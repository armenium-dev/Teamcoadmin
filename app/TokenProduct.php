<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TokenProduct extends Model
{
    protected $fillable = [
        'data',
        'token',
        'product_id',
        'url_svg',
        'artisan_theme_content',
    ];
}
