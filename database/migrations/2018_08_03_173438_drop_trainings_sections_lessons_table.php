<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class DropTrainingsSectionsLessonsTable
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class DropTrainingsSectionsLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists("module_section");

        Schema::table("lessons", function (Blueprint $table) {
            $table->dropForeign("lessons_section_id_foreign");
            $table->dropForeign("lessons_module_id_foreign");
        });
        Schema::dropIfExists("lessons");
        Schema::table("sections", function (Blueprint $table) {
            $table->dropForeign("sections_training_id_foreign");
        });
        Schema::dropIfExists("sections");
        Schema::dropIfExists("trainings");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create("trainings", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("slug");
            $table->text("description");
            $table->integer("price");
            $table->boolean("visible");
            $table->integer("order")->default(0);
            $table->timestamps();
        });
        Schema::create("sections", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("slug");
            $table->boolean("visible");
            $table->integer("training_id")->unsigned();
            $table->integer("order");
            $table->timestamps();

            // Foreign keys
            $table->foreign("training_id")->references("id")->on("trainings");
        });
        Schema::create("lessons", function (Blueprint $table) {
            $table->increments("id");
            $table->string("title");
            $table->string("slug");
            $table->boolean("visible");
            $table->string("image")->nullable();
            $table->integer("section_id")->unsigned();
            $table->integer("module_id")->unsigned();

            // Foreign keys
            $table->foreign("section_id")->references("id")->on("sections");
            $table->foreign("module_id")->references("id")->on("modules");

            $table->timestamps();
        });
    }
}
