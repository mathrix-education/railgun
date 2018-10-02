<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class TransferDisciplineVisibility
 * Move the visibility of disciplines from the disicpline table to the class_discipline table.
 * During the rollback if at least on if the line class_discipline has visible set to 1, the disciplines table will have
 * visible set to 1 too.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @since     4.1.1
 * @copyright Mathrix Education SA
 */
class TransferDisciplineVisibility extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update(
            "UPDATE class_discipline INNER JOIN disciplines ON class_discipline.discipline_id = disciplines.id "
            . "SET class_discipline.visible = disciplines.visible"
        );

        Schema::table("disciplines", function (Blueprint $table) {
            $table->dropColumn("visible");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("disciplines", function (Blueprint $table) {
            $table->boolean("visible")->default(true);
        });

        DB::update("UPDATE disciplines SET visible = 0");

        DB::update(
            "UPDATE disciplines INNER JOIN (SELECT discipline_id, visible FROM class_discipline WHERE "
            . "class_discipline.visible = 1 GROUP BY discipline_id,visible) AS lol ON disciplines.id = "
            . "lol.discipline_id SET disciplines.visible = lol.visible WHERE disciplines.id > 0;"
        );
    }
}
