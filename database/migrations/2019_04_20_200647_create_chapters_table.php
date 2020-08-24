<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChaptersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chapters', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug');
            $table->unsignedSmallInteger('order');
            $table->boolean('visible');
            $table->unsignedBigInteger('notion_count')->default(0);
            $table->unsignedBigInteger('activity_count')->default(0);
            $table->timestamps();

            // Foreign keys
            $table->foreignId('discipline_id')->constrained();
            $table->foreignId('grade_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
}
