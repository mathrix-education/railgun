<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateDisciplinesTable.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since 1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateDisciplinesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("disciplines", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("slug");
            $table->boolean("beta")->default(0);
            $table->boolean("visible")->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists("disciplines");
    }
}
