<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateDiscountsTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.0.3
 *
 * @copyright Mathrix Education SA
 */
class CreateDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("discounts", function (Blueprint $table) {
            $table->increments("id");
            $table->string("code");
            $table->string("type");
            $table->integer("value");
            $table->integer("counter");
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists("discounts");
    }
}
