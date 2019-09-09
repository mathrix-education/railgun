<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() : void
    {
        Schema::create('grades', static function (Blueprint $table) : void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug');
            $table->unsignedSmallInteger('order');
            $table->boolean('visible');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('country_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('parent_id')->references('id')->on('grades');
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::dropIfExists('grades');
    }
}
