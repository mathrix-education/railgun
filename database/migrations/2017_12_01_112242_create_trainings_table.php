<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateTrainingsTable.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since 1.0.1
 *
 * @copyright Mathrix Education SA
 */
class CreateTrainingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("trainings", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("slug");
            $table->text("description");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists("trainings");
    }
}
