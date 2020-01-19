<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackTrainingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('pack_training', static function (Blueprint $table) {
            $table->unsignedBigInteger('pack_id');
            $table->unsignedBigInteger('training_id');
            $table->unsignedTinyInteger('order');
            $table->primary(['pack_id', 'training_id']);

            // Foreign keys
            $table->foreign('pack_id')
                ->references('id')
                ->on('packs')
                ->onDelete('cascade');
            $table->foreign('training_id')
                ->references('id')
                ->on('trainings')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('pack_training');
    }
}
