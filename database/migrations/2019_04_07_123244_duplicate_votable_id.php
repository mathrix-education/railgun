<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DuplicateVotableId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("votes", function (Blueprint $table) {
            $table->unsignedInteger("comment_id")->after("user_id")->nullable();

            // Foreign
            $table->foreign("comment_id")->references("id")->on("comments");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("votes", function (Blueprint $table) {
            $table->dropColumn("comment_id");
        });
    }
}
