<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddStripeIdToPurchasesTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.0.3
 *
 * @copyright Mathrix Education SA
 */
class AddStripeIdToPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn("uuid", "stripe_id");
            $table->integer("refund")
                ->after("price");
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("purchases", function (Blueprint $table) {
            $table->renameColumn("stripe_id", "uuid");
            $table->dropColumn("refund");
        });
    }
}
