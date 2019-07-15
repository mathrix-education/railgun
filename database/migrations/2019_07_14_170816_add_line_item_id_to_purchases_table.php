<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLineItemIdToPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("purchases", function (Blueprint $table) {
            $table->unsignedBigInteger("line_item_id")->nullable()->after("transaction_id");

            // Foreign keys
            $table->foreign("transaction_id")->references("id")->on("transactions");
            $table->foreign("line_item_id")->references("id")->on("line_items");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("purchases", function (Blueprint $table) {
            // Foreign keys
            $table->dropForeign("purchases_line_item_id_foreign");
            $table->dropForeign("purchases_transaction_id_foreign");
        });

        Schema::table("purchases", function (Blueprint $table) {
            $table->dropColumn("line_item_id");
        });
    }
}
