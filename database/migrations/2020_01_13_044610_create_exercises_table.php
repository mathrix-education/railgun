<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercises', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['mcq']);
            $table->json('questions');
            $table->json('choices');
            $table->json('solutions');
            $table->json('explanations');
            $table->unsignedTinyInteger('order');
            $table->boolean('visible');
            $table->unsignedBigInteger('subject_id');
            $table->timestamps();
            $table->string('mapping');

            // Foreign keys
            $table->foreign('subject_id')->references('id')->on('subjects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exercises');
    }
}
