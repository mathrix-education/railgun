<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() : void
    {
        Schema::create('activities', static function (Blueprint $table) : void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('type');
            $table->boolean('visible');
            $table->unsignedSmallInteger('order');
            $table->boolean('premium');
            $table->boolean('reviewed');
            $table->unsignedBigInteger('notion_id');
            $table->unsignedInteger('module_count')->default(0);
            $table->timestamps();

            // Foreign keys
            $table->foreign('notion_id')->references('id')->on('notions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::dropIfExists('activities');
    }
}
