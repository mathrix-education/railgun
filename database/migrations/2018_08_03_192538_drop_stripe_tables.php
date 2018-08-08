<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropStripeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("subscriptions", function (Blueprint $table) {
            $table->dropForeign("subscriptions_user_id_foreign");
        });
        Schema::dropIfExists("subscriptions");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create("subscriptions", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("user_id")->unsigned();
            $table->string("name");
            $table->string("stripe_id");
            $table->string("stripe_plan");
            $table->integer("quantity");
            $table->timestamp("trial_ends_at")->nullable();
            $table->timestamp("ends_at")->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign("user_id")->references("id")->on("users");
        });
    }
}
