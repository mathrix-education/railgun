<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateDiscountProductTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class CreateDiscountProductTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("discount_product", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("discount_id")
                ->unsigned();
            $table->integer("product_id")
                ->unsigned();
            $table->timestamps();

            // Foreign keys
            $table->foreign("discount_id")
                ->references("id")
                ->on("discounts");
            $table->foreign("product_id")
                ->references("id")
                ->on("products");
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("discount_product", function (Blueprint $table) {
            $table->dropForeign("discount_product_discount_id_foreign");
            $table->dropForeign("discount_product_product_id_foreign");
        });
        Schema::dropIfExists("discount_product");
    }
}
