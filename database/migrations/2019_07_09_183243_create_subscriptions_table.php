<?php

use App\Services\Braintree\Models\Subscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->unique("braintree_id");
        });

        Schema::create("subscriptions", function (Blueprint $table) {
            $table->string("id")->primary();
            $table->enum("status", [
                Subscription::ACTIVE,
                Subscription::CANCELED,
                Subscription::EXPIRED,
                Subscription::WILL_EXPIRE,
                Subscription::PAST_DUE,
                Subscription::PENDING
            ]);
            $table->float("price");
            $table->float("next_billing_amount")->nullable();
            $table->unsignedTinyInteger("current_billing_cycle")->nullable();
            $table->unsignedTinyInteger("number_of_billing_cycles")->nullable();
            $table->timestamp("billing_period_start_date")->nullable();
            $table->timestamp("billing_period_end_date")->nullable();
            $table->timestamp("next_billing_date")->nullable();
            $table->string("customer_id")->nullable();
            $table->timestamps();

            // Foreign keys
            $table->unique("id");
            $table->foreign("customer_id")->references("braintree_id")->on("users");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("subscriptions");

        Schema::table("users", function (Blueprint $table) {
            $table->dropUnique("users_braintree_id_uindex");
        });
    }
}
