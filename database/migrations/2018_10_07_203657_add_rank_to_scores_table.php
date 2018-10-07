<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRankToScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("scores", function (Blueprint $table) {
            $table->integer("rank")->unsigned()->after("time");
            $table->dropColumn("time");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("scores", function (Blueprint $table) {
            $table->dropColumn("rank");
            $table->integer("time")->after("score");
        });
    }
}
