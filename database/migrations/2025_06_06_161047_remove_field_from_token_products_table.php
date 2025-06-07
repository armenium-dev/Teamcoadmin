<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveFieldFromTokenProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('token_products', function (Blueprint $table) {
            $table->dropColumn('artisan_theme_content');
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
            $table->string('artisan_theme_content', 20)->nullable()->default(null);
        });
    }
}
