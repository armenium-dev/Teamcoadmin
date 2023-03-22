<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToMailLogsTable extends Migration{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(){
		Schema::table('mail_logs', function(Blueprint $table){
			$table->integer('fail_id')->default(0)->after('job_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(){
		Schema::table('mail_logs', function(Blueprint $table){
			$table->dropColumn('fail_id');
		});
	}
}
