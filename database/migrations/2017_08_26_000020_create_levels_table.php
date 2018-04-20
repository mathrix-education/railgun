<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateLevelsTable
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class CreateLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("levels");
    }
}
