<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->increments("id");
            $table->string("name");
            $table->string("slug");
            $table->string("description");
            $table->boolean("premium");
            $table->enum("type", ["course", "test", "mcq", "mcq-table", "match", "fill", "flashcard"]);
            $table->text("content")->nullable();
            $table->text("questions")->nullable();
            $table->text("choices")->nullable();
            $table->text("solutions")->nullable();
            $table->text("corrections")->nullable();
            $table->text("options")->nullable();
            $table->boolean("visible")->default(true);
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
