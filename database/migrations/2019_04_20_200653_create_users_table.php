<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("users", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->string("username");
            $table->string("email");
            $table->string("firstname");
            $table->string("lastname");
            $table->string("password");
            $table->string("type");
            $table->uuid("token");
            $table->unsignedInteger("points");
            $table->unsignedInteger("level");
            $table->unsignedInteger("daily");
            $table->unsignedInteger("daily_achievements");
            $table->boolean("premium_bypass");
            $table->timestamp("premium_bypass_expires_at")->nullable();
            $table->unsignedBigInteger("grade_id");
            $table->string("stripe_id");
            $table->string("braintree_id");
            $table->json("settings");
            $table->timestamps();

            // Foreign keys
            $table->foreign("grade_id")->reference("id")->on("grades");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("users");
    }
}
