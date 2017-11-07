<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateClassDisciplineTable
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix
 */
class CreateClassDisciplineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("class_discipline", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("class_id")->unsigned();
            $table->integer("discipline_id")->unsigned();

            // Foreign keys
            $table->foreign("class_id")->references("id")->on("classes");
            $table->foreign("discipline_id")->references("id")->on("disciplines");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("class_discipline", function (Blueprint $table) {
            $table->dropForeign("class_discipline_class_id_foreign");
            $table->dropForeign("class_discipline_discipline_id_foreign");
        });

        Schema::dropIfExists("class_discipline");
    }
}
