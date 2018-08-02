<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBraintreeIdOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid("braintree_id")->after("stripe_id")->nullable();
            $table->dropColumn([
                "card_brand", "card_last_four", "trial_ends_at"
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("braintree_id")->after("stripe_id")->nullable();
            $table->string("card_brand")->after("stripe_id")->nullable();
            $table->string("card_last_four")->nullable();
            $table->timestamp("trial_ends_at")->nullable();
        });
    }
}
