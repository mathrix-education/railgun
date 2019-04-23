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
            $table->text("content")->nullable();
            $table->text("questions")->nullable();
            $table->text("choices")->nullable();
            $table->text("solutions")->nullable();
            $table->text("corrections")->nullable();
            $table->text("options")->nullable();
            $table->boolean("visible");
            $table->boolean("seo_index")->default(0);
            $table->string("seo_title")->nullable();
            $table->string("seo_slug")->nullable();
            $table->string("seo_keywords")->nullable();
            $table->string("seo_description")->nullable();
            $table->unsignedSmallInteger("order");
            $table->unsignedBigInteger("activity_id")->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign("activity_id")->references("id")->on("activities");
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
