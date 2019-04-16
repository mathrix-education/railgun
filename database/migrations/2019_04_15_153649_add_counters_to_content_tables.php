<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountersToContentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("chapters", function (Blueprint $table) {
            $table->unsignedInteger("activity_count")->default(0)->after("discipline_id");
            $table->unsignedInteger("notion_count")->default(0)->after("discipline_id");
        });
        Schema::table("notions", function (Blueprint $table) {
            $table->unsignedInteger("activity_count")->default(0)->after("chapter_id");
        });
        Schema::table("activities", function (Blueprint $table) {
            $table->unsignedInteger("module_count")->default(0)->after("notion_id");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("activities", function (Blueprint $table) {
            $table->dropColumn("module_count");
        });
        Schema::table("notions", function (Blueprint $table) {
            $table->dropColumn("activity_count");
        });
        Schema::table("chapters", function (Blueprint $table) {
            $table->dropColumn("activity_count");
            $table->dropColumn("notion_count");
        });
    }
}
