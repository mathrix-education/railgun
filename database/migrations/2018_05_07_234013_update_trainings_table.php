<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTrainingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("trainings", function (Blueprint $table) {
            $table->integer("price")->after("description");
            $table->integer("order")->after("description")->default(0);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("trainings", function (Blueprint $table) {
            $table->dropColumn("price");
            $table->dropColumn("order");
        });
    }
}
