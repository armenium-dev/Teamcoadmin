<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyFieldsInTokenProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::table('token_products', function (Blueprint $table) {
            $table->string('token', 255)->nullable()->default(null)->change();
            $table->bigInteger('product_id')->default(0)->change();
            $table->json('data')->nullable()->default(null)->change();
            $table->string('url_svg', 255)->nullable()->default(null)->change();
            $table->string('artisan_theme_content', 20)->nullable()->default(null)->change();
        });*/

        Schema::table('token_products', function (Blueprint $table) {
            $table->string('token', 255)->nullable()->default(null)->change();
        });

        Schema::table('token_products', function (Blueprint $table) {
            $table->bigInteger('product_id')->default(0)->change();
        });

        DB::statement('ALTER TABLE token_products MODIFY data JSON DEFAULT NULL');

        /*Schema::table('token_products', function (Blueprint $table) {
            $table->json('data')->nullable()->default(null)->change();
        });*/

        Schema::table('token_products', function (Blueprint $table) {
            $table->string('url_svg', 255)->nullable()->default(null)->change();
        });

        Schema::table('token_products', function (Blueprint $table) {
            $table->string('artisan_theme_content', 20)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*Schema::table('token_products', function (Blueprint $table) {
            //$table->string('token', 255)->change();
            //$table->bigInteger('product_id')->change();
            //$table->text('data')->change();
            //$table->text('url_svg')->change();
            //$table->longText('artisan_theme_content')->change();
        });*/
    }
}
