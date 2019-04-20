<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("discount_product", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("discount_id");
            $table->unsignedBigInteger("product_id");
            $table->timestamps();

            // Foreign keys
            $table->foreign("discount_id")->references("id")->on("discounts");
            $table->foreign("product_id")->references("id")->on("products");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("discount_product");
    }
}
