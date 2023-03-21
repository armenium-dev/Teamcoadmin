<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailLogsTable extends Migration{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up(){
		Schema::create('mail_logs', function(Blueprint $table){
			$table->increments('id');
			$table->integer('object_id')->default(0);
			$table->integer('sent')->default(0);
			$table->text('body')->nullable();
			$table->string('controller')->nullable();
			$table->integer('job_id')->default(0);
			$table->timestamps();
		});
	}
	
	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down(){
		Schema::dropIfExists('mail_logs');
	}
}
