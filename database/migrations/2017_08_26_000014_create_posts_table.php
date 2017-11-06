<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreatePostsTable
 *
 * @author Mathrix Back-End Team
 * @since 0.8.1
 * @copyright Mathrix
 */
class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("posts", function (Blueprint $table) {
            $table->increments("id");
            $table->string("title");
            $table->string("slug");
            $table->text("content");
            $table->integer("category_id")->unsigned();
            $table->integer("user_id")->unsigned();
            $table->dateTime("created_at");
            $table->dateTime("updated_at");

            // Foreign keys
            $table->foreign("category_id")->references("id")->on("categories");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("posts", function (Blueprint $table) {
            $table->dropForeign("posts_category_id_foreign");
        });

        Schema::dropIfExists("posts");
    }
}
