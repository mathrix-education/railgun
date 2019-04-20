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
            $table->string("firstname")->nullable();
            $table->string("lastname")->nullable();
            $table->string("password");
            $table->string("type");
            $table->uuid("token")->nullable();
            $table->unsignedInteger("points")->default(0);
            $table->unsignedInteger("level")->default(1);
            $table->unsignedInteger("daily")->default(0);
            $table->unsignedInteger("daily_achievements")->default(0);
            $table->boolean("premium_bypass")->default(0);
            $table->timestamp("premium_bypass_expires_at")->nullable();
            $table->unsignedBigInteger("grade_id")->nullable();
            $table->string("stripe_id")->nullable();
            $table->string("braintree_id")->nullable();
            $table->json("settings");
            $table->timestamps();

            // Foreign keys
            $table->foreign("grade_id")->references("id")->on("grades");
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
