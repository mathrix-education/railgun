<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateScoresTable.
 *
 * @author Mathrix Back-End Team
 *
 * @since 4.1.0
 *
 * @copyright Mathrix SA
 */
class CreateScoresTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("scores", function (Blueprint $table) {
            $table->increments("id");
            $table->string("game");
            $table->integer("score")->unsigned()->nullable();
            $table->integer("time")->unsigned()->nullable();
            $table->integer("user_id")->unsigned();
            $table->text("data")->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign("user_id")->references("id")->on("users");
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("scores", function (Blueprint $table) {
            $table->dropForeign("scores_user_id_foreign");
        });

        Schema::dropIfExists("scores");
    }
}
