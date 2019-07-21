<?php

use Braintree\TransactionLineItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("line_items", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->enum("kind", [TransactionLineItem::DEBIT, TransactionLineItem::CREDIT]);
            $table->string("name")->nullable();
            $table->unsignedBigInteger("product_code")->nullable();
            $table->string("product_type")->nullable();
            $table->unsignedTinyInteger("quantity");
            $table->float("total_amount")->nullable();
            $table->float("unit_amount")->nullable();
            $table->string("unit_of_measure")->nullable();
            $table->string("transaction_id")->nullable();

            // Foreign keys
            $table->foreign("transaction_id")->references("id")->on("transactions");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("line_items");
    }
}
