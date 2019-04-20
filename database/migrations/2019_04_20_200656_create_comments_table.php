<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->bigIncrements("id");
            $table->text("content");
            $table->unsignedBigInteger("parent_id")->nullable();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("module_id");
            $table->timestamp("deleted_at")->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign("module_id")->references("id")->on("modules");
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
        Schema::dropIfExists("comments");
    }
}
