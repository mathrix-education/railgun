<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCommentsTableChangeTypeFromEnumToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("comments", function (Blueprint $table) {
            $table->dropColumn("commentable_type");
        });
        Schema::table("comments", function (Blueprint $table) {
            $table->string("commentable_type");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("comments", function (Blueprint $table) {
            $table->dropColumn("commentable_type");
        });
        Schema::table("comments", function (Blueprint $table) {
            $table->enum("commentable_type", ["module", "post", "course"]);
        });
    }
}
