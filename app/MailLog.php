<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model{
	protected $fillable = [
		'sent',
		'object_id',
		'body',
		'controller',
		'job_id',
		'fail_id',
	];
}
