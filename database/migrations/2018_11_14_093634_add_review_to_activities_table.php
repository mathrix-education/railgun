<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddReviewToActivitiesTable
 * Add the "reviewed" column at the end of the activity table as asked in #161892592
 */
class AddReviewToActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("activities", function (Blueprint $table) {
            $table->boolean("reviewed")->default(false);
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
            $table->dropColumn("reviewed");
        });
    }
}
