<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateLessonsTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.1
 *
 * @copyright Mathrix Education SA
 */
class CreateLessonsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("lessons", function (Blueprint $table) {
            $table->increments("id");
            $table->string("title");
            $table->string("slug");
            $table->string("image")
                ->nullable();
            $table->integer("section_id")
                ->unsigned();
            $table->integer("module_id")
                ->unsigned();

            $table->foreign("section_id")
                ->references("id")
                ->on("sections");
            $table->foreign("module_id")
                ->references("id")
                ->on("modules");

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("lessons", function (Blueprint $table) {
            $table->dropForeign("lessons_section_id_foreign");
            $table->dropForeign("lessons_module_id_foreign");
        });
        Schema::dropIfExists("lessons");
    }
}
