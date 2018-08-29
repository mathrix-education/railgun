<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddBraintreeIdOnUsersTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class AddBraintreeIdOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid("braintree_id")
                ->after("stripe_id")
                ->nullable();
            $table->dropColumn([
                "card_brand", "card_last_four", "trial_ends_at",
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("braintree_id")
                ->after("stripe_id")
                ->nullable();
            $table->string("card_brand")
                ->after("stripe_id")
                ->nullable();
            $table->string("card_last_four")
                ->nullable();
            $table->timestamp("trial_ends_at")
                ->nullable();
        });
    }
}
