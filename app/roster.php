<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class roster extends Model{

	protected $fillable = [
		'reference',
		'comments',
		'number_color',
		'inside_color',
		'outside_color',
		'accessory_items',
		'settings',
		'shipping_method',
		'jersey_set_name',
		'short_set_name'
	];
	
	public static $default_settings = [
		'section_1'  => ['title' => '1. Contact and Shipping Information'],
		'section_2'  => ['title' => '2. Shipping Method'],
		'section_3'  => ['title' => '3. Jersey Details'],
		'section_4'  => ['title' => '4. Accessory Items'],
		'section_5'  => ['title' => '5. Number Colors'],
		'section_6'  => ['title' => '6. Artwork Placement and Order Description'],
		'section_7'  => ['title' => '7. Jersey Quantities'],
		'section_8'  => ['title' => '8. Shorts or Socks Quantities'],
		'section_9'  => ['title' => '9. Team Roster', 'table_head' => [
			'head_1' => '-',
			'head_2' => 'Jersey Size',
			'head_3' => 'Jersey #',
			'head_4' => 'Jersey Name',
			'head_5' => 'Notes',
			'head_6' => 'Shorts Size',
		]],
		'section_10'  => ['title' => '10. Attach Logo(s)'],
		'section_11' => ['title' => '11. Resend email to:'],
	];
	
	public static function boot(){
		parent::boot();
		
		self::creating(function($model){
			if(is_null($model->settings)){
				$model->settings = json_encode(self::$default_settings);
			}
		});
		
		self::created(function($model){
			// ... code here
		});
		
		self::updating(function($model){
			// ... code here
		});
		
		self::updated(function($model){
			// ... code here
		});
		
		self::deleting(function($model){
			// ... code here
		});
		
		self::deleted(function($model){
			// ... code here
		});
	}
	
	public function client(){
		return $this->belongsTo(client::class);
	}
	
	public function files(){
		return $this->belongsToMany(file::class);
	}
	
	public function jersey(){
		return $this->hasOne(jersey_detail::class);
	}
	
	public function quantities(){
		return $this->hasMany(quantity::class);
	}
	
	public function teams(){
		return $this->hasMany(team::class);
	}
	
	public function quantitySumByType($type){
		$quantity = $this->hasMany(quantity::class)->where(['type' => $type]);
		
		return $quantity->sum('quantity');
	}
	
}
