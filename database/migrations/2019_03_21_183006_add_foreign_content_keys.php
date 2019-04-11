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
            $table->timestamps();
        });
        Schema::table("activities", function (Blueprint $table) {
            $table->unsignedInteger("notion_id")->after("reviewed")->nullable();
            $table->unsignedInteger("order")->after("visible")->nullable();
            $table->timestamps();
        });
        Schema::table("modules", function (Blueprint $table) {
            $table->unsignedInteger("activity_id")->after("visible")->nullable();
            $table->unsignedInteger("order")->after("visible")->nullable();
            $table->string("seo_description")->after("visible")->nullable();
            $table->string("seo_keywords")->after("visible")->nullable();
            $table->string("seo_slug")->after("visible")->nullable();
            $table->string("seo_title")->after("visible")->nullable();
            $table->boolean("seo_index")->after("visible")->default(0);
            $table->timestamps();
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
            $table->dropColumn("updated_at");
            $table->dropColumn("created_at");
            $table->dropColumn("activity_id");
            $table->dropColumn("order");
            $table->dropColumn("seo_index");
            $table->dropColumn("seo_title");
            $table->dropColumn("seo_slug");
            $table->dropColumn("seo_keywords");
            $table->dropColumn("seo_description");
        });
        Schema::table("activities", function (Blueprint $table) {
            $table->dropColumn("updated_at");
            $table->dropColumn("created_at");
            $table->dropColumn("notion_id");
            $table->dropColumn("order");
        });
        Schema::table("notions", function (Blueprint $table) {
            $table->dropColumn("updated_at");
            $table->dropColumn("created_at");
            $table->dropColumn("chapter_id");
            $table->dropColumn("order");
        });
    }
}
