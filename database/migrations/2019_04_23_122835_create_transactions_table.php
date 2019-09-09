<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() : void
    {
        Schema::create('transactions', static function (Blueprint $table) : void {
            $table->string('id', 36)->unique();
            $table->string('type');
            $table->string('plan_id')->nullable();
            $table->float('amount');
            $table->float('refunded_amount')->default(0);
            $table->string('refunded_transaction_id', 36)->nullable();
            $table->string('customer_id', 36);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::dropIfExists('transactions');
    }
}
