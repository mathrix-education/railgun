<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateCommentsTable
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix
 */
class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("comments", function (Blueprint $table) {
            $table->increments("id");
            $table->text("content")->nullable();
            $table->integer("score")->default(0);
            $table->integer("parent_id")->unsigned()->nullable();
            $table->integer("user_id")->unsigned()->nullable();
            $table->dateTime("created_at");
            $table->dateTime("updated_at");
            $table->dateTime("deleted_at")->nullable();

            // Polymorphic
            $table->integer("commentable_id")->unsigned();
            $table->enum("commentable_type", ["module", "post", "course"]);

            // Foreign keys
            $table->foreign("parent_id")->references("id")->on("comments");
            $table->foreign("user_id")->references("id")->on("users");
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
            $table->dropForeign("comments_parent_id_foreign");
            $table->dropForeign("comments_user_id_foreign");
        });

        Schema::dropIfExists("comments");
    }
}
