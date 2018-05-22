<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateUsersTable
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
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
            $table->increments("id");
            $table->string("username")->nullable();
            $table->string("email");
            $table->string("firstname")->nullable();
            $table->string("lastname")->nullable();
            $table->string("password");
            $table->enum("type", ["registered", "moderator", "admin"]);
            $table->string("token", 36);
            $table->integer("points")->default(0);
            $table->integer("level")->default(1);
            $table->integer("daily")->default(10);
            $table->integer("daily_achievements")->default(0);
            $table->text("settings");
            $table->integer("class_id")->unsigned()->nullable();

            // Stripe
            $table->string("stripe_id")->nullable();
            $table->string("card_brand")->nullable();
            $table->string("card_last_four")->nullable();
            $table->timestamp("trial_ends_at")->nullable();

            // Foreign keys
            $table->foreign("class_id")->references("id")->on("classes");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropForeign("users_class_id_foreign");
        });

        Schema::dropIfExists("users");
    }
}
