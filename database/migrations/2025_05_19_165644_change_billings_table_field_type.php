<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBillingsTableFieldType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->longText('name')->change();
            $table->longText('company')->change();
            $table->longText('address')->change();
            $table->longText('address_2')->change();
            $table->longText('city')->change();
            $table->longText('state')->change();
            $table->longText('zip')->change();
            $table->longText('country')->change();
            $table->longText('email')->change();
            $table->longText('phone')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('company', 255)->change();
            $table->string('address', 255)->change();
            $table->string('address_2', 255)->change();
            $table->string('city', 255)->change();
            $table->string('state', 255)->change();
            $table->string('zip', 255)->change();
            $table->string('country', 255)->change();
            $table->string('email', 255)->change();
            $table->string('phone', 255)->change();
        });
    }
}
