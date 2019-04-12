<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateActivityNotionTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateActivityNotionTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("activity_notion", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("activity_id")
                ->unsigned();
            $table->integer("notion_id")
                ->unsigned();
            $table->integer("order")
                ->default(0);

            // Foreign keys
            $table->foreign("activity_id")
                ->references("id")
                ->on("activities");
            $table->foreign("notion_id")
                ->references("id")
                ->on("notions");
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("activity_notion", function (Blueprint $table) {
            $table->dropForeign("activity_notion_activity_id_foreign");
            $table->dropForeign("activity_notion_notion_id_foreign");
        });

        Schema::dropIfExists("activity_notion");
    }
}
