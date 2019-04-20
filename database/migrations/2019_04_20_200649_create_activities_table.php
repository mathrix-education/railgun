<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("activities", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("name");
            $table->string("type");
            $table->boolean("visible");
            $table->unsignedSmallInteger("order");
            $table->boolean("premium");
            $table->boolean("reviewed");
            $table->unsignedBigInteger("notion_id");
            $table->unsignedInteger("module_count");
            $table->timestamps();

            // Foreign keys
            $table->foreign("notion_id")->reference("id")->on("notions");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("activities");
    }
}
