<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradeTrainingTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grade_training', static function (Blueprint $table): void {
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('training_id');
            $table->primary(['grade_id', 'training_id']);

            // Foreign keys
            $table->foreign('grade_id')
                ->references('id')
                ->on('grades')
                ->onDelete('cascade');
            $table->foreign('training_id')
                ->references('id')
                ->on('trainings')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_training');
    }
}
