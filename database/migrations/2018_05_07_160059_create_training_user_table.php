<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainingUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("training_user", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("training_id")->unsigned();
            $table->integer("user_id")->unsigned();

            // Foreign keys
            $table->foreign("training_id")->references("id")->on("trainings");
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
        Schema::table("training_user", function (Blueprint $table) {
            $table->dropForeign("training_user_training_id_foreign");
            $table->dropForeign("training_user_user_id_foreign");
        });
        Schema::dropIfExists("training_user");
    }
}
