<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->bigIncrements("id");
            $table->string("title");
            $table->string("slug");
            $table->unsignedBigInteger("module_id");
            $table->unsignedBigInteger("category_id");
            $table->unsignedBigInteger("user_id");
            $table->timestamps();

            // Foreign keys
            $table->foreign("category_id")->references("id")->on("categories");
            $table->foreign("module_id")->references("id")->on("modules");
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
        Schema::dropIfExists("posts");
    }
}
