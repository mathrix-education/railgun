<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateSubscriptionsTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("subscriptions", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("user_id")
                ->unsigned();
            $table->string("name");
            $table->string("stripe_id");
            $table->string("stripe_plan");
            $table->integer("quantity");
            $table->timestamp("trial_ends_at")
                ->nullable();
            $table->timestamp("ends_at")
                ->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign("user_id")
                ->references("id")
                ->on("users");
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists("subscriptions");
    }
}
