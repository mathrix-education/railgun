<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddSEOLastModOnActivitiesTable
 */
class AddSEOLastModOnPathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("paths", function (Blueprint $table) {
            $table->date("seo_lastmod")
                ->after("seo_index")
                ->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("paths", function (Blueprint $table) {
            $table->dropColumn("seo_lastmod");
        });
    }
}
