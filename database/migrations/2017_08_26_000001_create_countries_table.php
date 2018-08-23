<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateCountriesTable.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since 1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("countries", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("name_short");
            $table->string("slug");
            $table->string("slug_short");
            $table->boolean("visible")->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists("countries");
    }
}
