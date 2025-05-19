<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeRostersTableFieldType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rosters', function (Blueprint $table) {
            $table->longText('number_color')->change();
            $table->longText('accessory_items')->change();
            $table->longText('reference')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rosters', function (Blueprint $table) {
            $table->string('reference', 255)->change();
            $table->string('number_color', 50)->change();
            $table->text('accessory_items')->change();
        });
    }
}
