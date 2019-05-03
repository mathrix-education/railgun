<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateToScopesOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->json("scopes")->nullable()->after("type");
        });

        DB::update("UPDATE users SET scopes = CONCAT('[\"', type, '\"]')");
        DB::update("UPDATE users SET scopes = '[\"*\"]' WHERE scopes like '%admin%'");

        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("type");
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
            $table->string("type")->after("password");
            $table->dropColumn("scopes");
        });
    }
}
