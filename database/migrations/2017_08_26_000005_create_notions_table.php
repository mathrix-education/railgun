<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->integer('chapter_id')->unsigned();

            // Foreign keys
            $table->foreign('chapter_id')->references('id')->on('chapters');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notions', function (Blueprint $table) {
            $table->dropForeign('notions_chapter_id_foreign');
        });

        Schema::dropIfExists('notions');
    }
}
