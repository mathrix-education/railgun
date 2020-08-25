<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exams', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug');
            $table->unsignedTinyInteger('duration')->nullable();
            $table->json('content');
            $table->unsignedTinyInteger('order');
            $table->boolean('premium');
            $table->boolean('visible');
            $table->foreignId('chapter_id')->constrained();
            $table->timestamps();
            $table->string('mapping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
}
