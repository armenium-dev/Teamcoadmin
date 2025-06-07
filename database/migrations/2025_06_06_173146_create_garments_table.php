<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGarmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('garments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 255)->nullable()->default(null);
            $table->string('title', 255)->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->string('main_image', 255)->nullable()->default(null);
            $table->string('size_image', 255)->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('garments');
    }
}
