<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("paths", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("country_id")->unsigned();
            $table->integer("discipline_id")->unsigned();
            $table->integer("class_id")->unsigned();
            $table->integer("chapter_id")->unsigned();
            $table->integer("notion_id")->unsigned();
            $table->integer("activity_id")->unsigned();
            $table->integer("module_id")->unsigned();

            $table->foreign("country_id")
                ->references("id")
                ->on("countries");
            $table->foreign("class_id")
                ->references("id")
                ->on("classes");
            $table->foreign("discipline_id")
                ->references("id")
                ->on("disciplines");
            $table->foreign("chapter_id")
                ->references("id")
                ->on("chapters");
            $table->foreign("notion_id")
                ->references("id")
                ->on("notions");
            $table->foreign("activity_id")
                ->references("id")
                ->on("activities");
            $table->foreign("module_id")
                ->references("id")
                ->on("modules");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("paths", function (Blueprint $table) {
            $table->dropForeign("metas_user_id_foreign");
            $table->dropForeign("metas_country_id_foreign");
            $table->dropForeign("metas_class_id_foreign");
            $table->dropForeign("metas_discipline_id_foreign");
            $table->dropForeign("metas_chapter_id_foreign");
            $table->dropForeign("metas_notion_id_foreign");
            $table->dropForeign("metas_activity_id_foreign");
            $table->dropForeign("metas_module_id_foreign");
        });

        Schema::dropIfExists("paths");
    }
}
