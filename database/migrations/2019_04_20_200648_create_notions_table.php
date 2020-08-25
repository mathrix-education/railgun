<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notions', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('type');
            $table->boolean('visible');
            $table->unsignedSmallInteger('order');
            $table->foreignId('chapter_id')->constrained();
            $table->unsignedSmallInteger('activity_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notions');
    }
}
