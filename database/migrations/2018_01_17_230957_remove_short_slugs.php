<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveShortSlugs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("countries", function (Blueprint $table) {
            $table->dropColumn("name_short");
            $table->dropColumn("slug");
            $table->renameColumn("slug_short", "slug");
        });

        Schema::table("classes", function (Blueprint $table) {
            $table->dropColumn("name_short");
            $table->dropColumn("slug");
            $table->renameColumn("slug_short", "slug");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("countries", function (Blueprint $table) {
            $table->string("name_short")->after("name");
            $table->renameColumn("slug", "slug_short");
        });
        Schema::table("countries", function (Blueprint $table) {
            $table->string("slug")->after("name_short");
        });

        Schema::table("classes", function (Blueprint $table) {
            $table->string("name_short")->after("name");
            $table->renameColumn("slug", "slug_short");
        });
        Schema::table("classes", function (Blueprint $table) {
            $table->string("slug")->after("name_short");
        });
    }
}
