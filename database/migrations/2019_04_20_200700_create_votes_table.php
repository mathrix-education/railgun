<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("votes", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->tinyInteger("value");
            $table->unsignedBigInteger("comment_id");
            $table->unsignedBigInteger("user_id");
            $table->timestamps();

            // Foreign keys
            $table->foreign("comment_id")->references("id")->on("comments");
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
        Schema::dropIfExists("votes");
    }
}
