<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class DropScoreOnCommentsTable.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since 4.0.3
 *
 * @copyright Mathrix Education SA
 */
class DropScoreOnCommentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table("comments", function (Blueprint $table) {
            $table->dropColumn("score");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("comments", function (Blueprint $table) {
            $table->integer("score")->after("content");
        });
    }
}
