<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
