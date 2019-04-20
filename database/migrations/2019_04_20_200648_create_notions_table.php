<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("notions", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("name");
            $table->string("type");
            $table->boolean("visible");
            $table->unsignedSmallInteger("order");
            $table->unsignedBigInteger("chapter_id");
            $table->unsignedBigInteger("activity_count");
            $table->timestamps();

            // Foreign keys

            $table->foreign("chapter_id")->reference("id")->on("chapters");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("notions");
    }
}
