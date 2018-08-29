<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpirationToDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table("discounts", function (Blueprint $table) {
            $table->timestamp("expire_at")
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("discounts", function (Blueprint $table) {
            $table->dropColumn("expire_at");
        });
    }
}
