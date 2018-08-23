<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class UpdateTrancateCommentsTable.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since 1.0.1
 *
 * @copyright Mathrix Education SA
 */
class UpdateTrancateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table("comments", function (Blueprint $table) {
            $table->dropColumn("commentable_type");
        });
        Schema::table("comments", function (Blueprint $table) {
            $table->enum("commentable_type", ["notion", "post", "course"]);
        });
    }

    /**
     * Reverse the migrations.
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
