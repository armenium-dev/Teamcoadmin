<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Garment extends Model
{
    protected $table = 'garments';

    protected $fillable = [
        'code',
        'title',
        'description',
        'main_image',
        'size_image',
    ];
}
