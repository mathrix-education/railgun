<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChaptersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("chapters", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("name");
            $table->string("slug");
            $table->unsignedSmallInteger("order");
            $table->boolean("visible");
            $table->unsignedBigInteger("discipline_id");
            $table->unsignedBigInteger("grade_id");
            $table->unsignedBigInteger("notion_count");
            $table->unsignedBigInteger("activity_count");
            $table->timestamps();

            // Foreign keys
            $table->foreign("discipline_id")->reference("id")->on("disciplines");
            $table->foreign("grade_id")->reference("id")->on("grades");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("chapters");
    }
}
