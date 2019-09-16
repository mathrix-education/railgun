<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductProductTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_product', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('child_id');
            $table->unsignedBigInteger('parent_id');
            $table->unsignedSmallInteger('order');

            // Foreign keys
            $table->foreign('child_id')->references('id')->on('products');
            $table->foreign('parent_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_product');
    }
}
