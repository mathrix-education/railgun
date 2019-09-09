<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() : void
    {
        Schema::create('products', static function (Blueprint $table) : void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug');
            $table->string('image')->nullable();
            $table->string('type');
            $table->text('description')->nullable();
            $table->float('price')->nullable();
            $table->boolean('visible');
            $table->unsignedSmallInteger('order');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('module_id')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('parent_id')->references('id')->on('products');
            $table->foreign('module_id')->references('id')->on('modules');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::dropIfExists('products');
    }
}
