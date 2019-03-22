<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignContentKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("notions", function (Blueprint $table) {
            $table->unsignedInteger("chapter_id")->after("visible")->nullable();
            $table->unsignedInteger("order")->after("visible")->nullable();
        });
        Schema::table("activities", function (Blueprint $table) {
            $table->unsignedInteger("notion_id")->after("reviewed")->nullable();
            $table->unsignedInteger("order")->after("visible")->nullable();
        });
        Schema::table("modules", function (Blueprint $table) {
            $table->unsignedInteger("activity_id")->after("visible")->nullable();
            $table->unsignedInteger("order")->after("visible")->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("modules", function (Blueprint $table) {
            $table->dropColumn("activity_id");
            $table->dropColumn("order");
        });
        Schema::table("activities", function (Blueprint $table) {
            $table->dropColumn("notion_id");
            $table->dropColumn("order");
        });
        Schema::table("notions", function (Blueprint $table) {
            $table->dropColumn("chapter_id");
            $table->dropColumn("order");
        });
    }
}
