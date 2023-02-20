<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsBillingTable extends Migration{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(){
		Schema::create('billings', function(Blueprint $table){
			$table->increments('id');
			$table->integer('client_id')->default(0);
			$table->string('name')->nullable();
			$table->string('company')->nullable();
			$table->string('address')->nullable();
			$table->string('address_2')->nullable();
			$table->string('city')->nullable();
			$table->string('state')->nullable();
			$table->string('zip')->nullable();
			$table->string('country')->nullable();
			$table->string('email')->nullable();
			$table->string('phone')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(){
		Schema::dropIfExists('billings');
	}
}
