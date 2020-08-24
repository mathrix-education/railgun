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
            $table->unsignedSmallInteger('order');

            // Foreign keys
            $table->foreignId('child_id')->constrained('products');
            $table->foreignId('parent_id')->constrained('products');
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
