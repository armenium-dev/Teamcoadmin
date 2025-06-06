<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddField1ToTokenProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('token_products', function (Blueprint $table) {
            $table->longText('artisan_theme_content')->after('url_svg');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('token_products', function (Blueprint $table) {
            $table->dropColumn('artisan_theme_content');
        });
    }
}
