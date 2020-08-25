<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExercisesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exercises', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->enum('type', ['mcq']);
            $table->json('questions');
            $table->json('choices');
            $table->json('solutions');
            $table->json('explanations');
            $table->unsignedTinyInteger('order');
            $table->boolean('premium');
            $table->boolean('visible');
            $table->foreignId('subject_id')->constrained();
            $table->timestamps();
            $table->string('mapping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
}
