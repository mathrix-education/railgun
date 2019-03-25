<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CopyClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("grades", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("slug");
            $table->integer("order")->default(0);
            $table->boolean("visible")->default(true);
            $table->integer("parent_id")->unsigned()->nullable();
            $table->integer("country_id")->unsigned();

            // Foreign keys
            $table->foreign("parent_id")->references("id")->on("classes");
            $table->foreign("country_id")->references("id")->on("countries");
        });

        Schema::create("discipline_grade", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("discipline_id")->unsigned();
            $table->integer("grade_id")->unsigned();
            $table->boolean("visible")->unsigned();
            $table->integer("order")->unsigned();

            // Foreign keys
            $table->foreign("discipline_id")->references("id")->on("disciplines");
            $table->foreign("grade_id")->references("id")->on("grades");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("grades");
        Schema::dropIfExists("discipline_grade");
    }
}
