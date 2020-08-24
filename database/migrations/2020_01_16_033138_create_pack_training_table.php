<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackTrainingTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pack_training', static function (Blueprint $table): void {
            $table->unsignedTinyInteger('order');
            $table->primary(['pack_id', 'training_id']);

            // Foreign keys
            $table->foreignId('pack_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('training_id')
                ->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pack_training');
    }
}
