<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class UpdateLessonsTable
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 4.0.0
 * @copyright Mathrix Education SA
 */
class UpdateLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("lessons", function (Blueprint $table) {
            $table->integer("order")->after("image")->default(0);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("lessons", function (Blueprint $table) {
            $table->dropColumn("order");
        });
    }
}
