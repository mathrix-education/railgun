<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityNotionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_notion', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('activity_id')->unsigned();
            $table->integer('notion_id')->unsigned();

            // Foreign keys
            $table->foreign('activity_id')->references('id')->on('activities');
            $table->foreign('notion_id')->references('id')->on('notions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_notion', function (Blueprint $table) {
            $table->dropForeign('activity_notion_activity_id_foreign');
            $table->dropForeign('activity_notion_notion_id_foreign');
        });

        Schema::dropIfExists('activity_notion');
    }
}
