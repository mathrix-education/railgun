<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpvotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upvotes', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type');
            $table->integer('user_id')->unsigned()->nullable();
            $table->dateTime('created_at');

            // Polymorphic
            $table->integer('upvotable_id')->unsigned();
            $table->enum('upvotable_type', ['comment']);

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('upvotes', function (Blueprint $table) {
            $table->dropForeign('upvotes_user_id_foreign');
        });

        Schema::dropIfExists('upvotes');
    }
}
