<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateLevelsTable.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since 1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateLevelsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("levels", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("level");
            $table->integer("objective");
            $table->string("youtube_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists("levels");
    }
}
