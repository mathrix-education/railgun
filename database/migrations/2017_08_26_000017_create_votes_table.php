<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateVotesTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateVotesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("votes", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("value");
            $table->integer("user_id")
                ->unsigned()
                ->nullable();
            $table->dateTime("created_at");
            $table->dateTime("updated_at");

            // Polymorphic
            $table->integer("votable_id")
                ->unsigned();
            $table->enum("votable_type", ["comment"]);

            // Foreign keys
            $table->foreign("user_id")
                ->references("id")
                ->on("users");
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("votes", function (Blueprint $table) {
            $table->dropForeign("votes_user_id_foreign");
        });

        Schema::dropIfExists("votes");
    }
}
