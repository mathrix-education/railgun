<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSeoToActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("activities", function (Blueprint $table) {
            $table->boolean("premium")->after("visible");
            $table->string("seo_title")->nullable()->after("slug");
            $table->text("seo_description")->nullable()->after("seo_title");
        });

        DB::statement("UPDATE activities SET seo_title = name_seo");

        Schema::table("activities", function (Blueprint $table) {
            $table->dropColumn("name_seo");
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
            $table->string("name_seo")->after("slug");
        });

        DB::statement("UPDATE activities SET name_seo = seo_title");

        Schema::table("activities", function (Blueprint $table) {
            $table->dropColumn("premium");
            $table->dropColumn("seo_title");
            $table->dropColumn("seo_description");
        });
    }
}
