<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProblemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->unsignedTinyInteger('duration');
            $table->unsignedTinyInteger('order');
            $table->boolean('premium');
            $table->boolean('protected');
            $table->timestamp('frozen_at')->nullable();
            $table->boolean('visible');
            $table->foreignId('notion_id');
            $table->timestamps();

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
        Schema::dropIfExists('problems');
    }
}
