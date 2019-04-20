<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("modules", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("type");
            $table->text("content");
            $table->text("questions");
            $table->text("choices");
            $table->text("solutions");
            $table->text("corrections");
            $table->text("options");
            $table->boolean("visible");
            $table->boolean("seo_index");
            $table->string("seo_title");
            $table->string("seo_slug");
            $table->string("seo_keywords");
            $table->string("seo_description");
            $table->unsignedSmallInteger("order");
            $table->unsignedBigInteger("activity_id");
            $table->timestamps();

            // Foreign keys

            $table->foreign("activity_id")->reference("id")->on("activities");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("modules");
    }
}
