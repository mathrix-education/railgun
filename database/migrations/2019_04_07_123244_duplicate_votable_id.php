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

        Schema::table("products", function(Blueprint $table) {
            $table->unsignedInteger("parent_id")->after("order")->nullable();

            // Foreign
            $table->foreign("parent_id")->references("id")->on("products");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("products", function (Blueprint $table) {
            $table->dropForeign("products_parent_id_foreign");
            $table->dropColumn("product_id");
        });

        Schema::table("votes", function (Blueprint $table) {
            $table->dropForeign("votes_comment_id_foreign");
            $table->dropColumn("comment_id");
        });
    }
}
