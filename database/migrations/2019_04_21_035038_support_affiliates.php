<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SupportAffiliates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->tinyInteger("affiliate_factor")->default(0)->after("premium_bypass_expires_at");
        });
        Schema::table("purchases", function (Blueprint $table) {
            $table->string("transaction_id")->nullable()->after("id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("purchases", function (Blueprint $table) {
            $table->dropColumn("transaction_id");
        });
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("affiliate_factor");
        });
    }
}
