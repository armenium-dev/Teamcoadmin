<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class client extends Model
{
    protected $fillable = [
        'name','company','address','address_2','state','zip','country','email','phone','city'
    ];

	public function billing(){
		return $this->hasOne(billing::class);
	}

	public function quote(){
    	return $this->hasOne(quote::class);
    }
    public function roster(){
    	return $this->hasOne(roster::class);
    }
    public function design(){
    	return $this->hasOne(design::class);
    }
}
