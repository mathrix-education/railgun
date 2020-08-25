<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sections', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug');
            $table->unsignedTinyInteger('order');
            $table->boolean('visible');
            $table->foreignId('training_id')->constrained();
            $table->timestamps();
            $table->string('mapping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
}
