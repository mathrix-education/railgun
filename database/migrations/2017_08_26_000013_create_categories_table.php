<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateCategoriesTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("categories", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("slug");
            $table->string("image")
                ->nullable()
                ->default(null);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists("categories");
    }
}
