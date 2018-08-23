<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateChaptersTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateChaptersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("chapters", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("slug");
            $table->integer("order")
                ->default(0);
            $table->boolean("visible")
                ->default(true);
            $table->integer("class_id")
                ->unsigned();
            $table->integer("discipline_id")
                ->unsigned();

            // Foreign keys
            $table->foreign("class_id")
                ->references("id")
                ->on("classes");
            $table->foreign("discipline_id")
                ->references("id")
                ->on("disciplines");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("chapters", function (Blueprint $table) {
            $table->dropForeign("chapters_class_id_foreign");
            $table->dropForeign("chapters_discipline_id_foreign");
        });

        Schema::dropIfExists("chapters");
    }
}
