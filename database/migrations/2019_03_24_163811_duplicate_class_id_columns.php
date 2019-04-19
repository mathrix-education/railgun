<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DuplicateClassIdColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("chapters", function (Blueprint $table) {
            $table->unsignedInteger("grade_id")->nullable()->after("class_id");
            $table->foreign("grade_id")->references("id")->on("grades");
        });

       Schema::table("users", function (Blueprint $table) {
            $table->unsignedInteger("grade_id")->nullable()->after("class_id");
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
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("grade_id");
        });
        Schema::table("chapters", function (Blueprint $table) {
            $table->dropColumn("grade_id");
        });
    }
}
