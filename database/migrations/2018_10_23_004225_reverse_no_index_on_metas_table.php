<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReverseNoIndexOnMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("metas", function (Blueprint $table) {
            $table->boolean("seo_index")->after("no_index");
        });

        DB::statement("UPDATE metas SET seo_index = 1^no_index");

        Schema::table("metas", function (Blueprint $table) {
            $table->dropColumn("no_index");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("metas", function (Blueprint $table) {
            $table->boolean("no_index")->after("seo_index");
        });

        DB::statement("UPDATE metas SET no_index = 1^seo_index");

        Schema::table("metas", function (Blueprint $table) {
            $table->dropColumn("seo_index");
        });
    }
}
