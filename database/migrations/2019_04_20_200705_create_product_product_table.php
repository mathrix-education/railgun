<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("product_product", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("child_id");
            $table->unsignedBigInteger("parent_id");
            $table->unsignedSmallInteger("order");

            // Foreign keys
            $table->foreign("child_id")->references("id")->on("products");
            $table->foreign("parent_id")->references("id")->on("products");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("product_product");
    }
}
