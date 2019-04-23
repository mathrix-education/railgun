<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("transactions", function (Blueprint $table) {
            $table->string("id", 36)->unique();
            $table->string("type");
            $table->string("plan_id")->nullable();
            $table->float("amount");
            $table->float("refunded_amount")->default(0);
            $table->string("refunded_transaction_id", 36)->nullable();
            $table->string("customer_id", 36);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("transactions");
    }
}
