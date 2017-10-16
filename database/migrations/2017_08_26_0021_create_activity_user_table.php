<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('activity_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->boolean('favorite')->default(false);
            $table->enum('status', ['completed', 'to_revise'])->nullable()->default(null);

            // Foreign keys
            $table->foreign('activity_id')->references('id')->on('activities');
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
        Schema::table('activity_user', function (Blueprint $table) {
            $table->dropForeign('activity_user_activity_id_foreign');
            $table->dropForeign('activity_user_user_id_foreign');
        });

        Schema::dropIfExists('levels');
    }
}
