<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class billing extends Model{

	protected $fillable = [
		'client_id',
		'name',
		'company',
		'address',
		'address_2',
		'city',
		'state',
		'zip',
		'country',
		'email',
		'phone',
	];

	public function client(){
		return $this->belongsTo(client::class);
	}

}
