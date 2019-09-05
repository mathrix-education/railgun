<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("level");
        });

        (new CreateLevelsTable())->down();
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        (new CreateLevelsTable())->up();

        Schema::table("users", function (Blueprint $table) {
            $table->unsignedInteger("level")->after("token");
        });
    }
}
