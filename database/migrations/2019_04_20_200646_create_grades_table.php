<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("grades", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("name");
            $table->string("slug");
            $table->unsignedSmallInteger("order");
            $table->boolean("visible");
            $table->unsignedBigInteger("parent_id")->nullable();
            $table->unsignedBigInteger("country_id");
            $table->timestamps();

            // Foreign keys
            $table->foreign("parent_id")->references("id")->on("grades");
            $table->foreign("country_id")->references("id")->on("countries");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("grades");
    }
}
