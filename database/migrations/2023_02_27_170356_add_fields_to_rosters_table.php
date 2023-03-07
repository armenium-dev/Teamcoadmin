<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToRostersTable extends Migration{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(){
		Schema::table('rosters', function(Blueprint $table){
			$table->string('short_set_name')->nullable()->after('accessory_items');
			$table->string('jersey_set_name')->nullable()->after('accessory_items');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(){
		Schema::table('rosters', function(Blueprint $table){
			$table->dropColumn(['short_set_name', 'jersey_set_name']);
		});
	}
}
