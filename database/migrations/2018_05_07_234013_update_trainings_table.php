<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class UpdateTrainingsTable.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since 4.0.0
 *
 * @copyright Mathrix Education SA
 */
class UpdateTrainingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table("trainings", function (Blueprint $table) {
            $table->integer("price")->after("description");
            $table->integer("order")->after("description")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("trainings", function (Blueprint $table) {
            $table->dropColumn("price");
            $table->dropColumn("order");
        });
    }
}
