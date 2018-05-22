<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class DropScoreOnCommentsTable
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
class DropScoreOnCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("comments", function (Blueprint $table) {
            $table->dropColumn("score");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("comments", function (Blueprint $table) {
            $table->integer("score")->after("content");
        });
    }
}
