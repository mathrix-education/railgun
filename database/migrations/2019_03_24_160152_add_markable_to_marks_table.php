<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMarkableToMarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("marks", function (Blueprint $table) {
            $table->unsignedBigInteger("markable_id")->after("status");
            $table->string("markable_type")->after("status");
            $table->index(["markable_type", "markable_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("marks", function (Blueprint $table) {
            $table->dropColumn(["markable_id", "markable_type"]);
        });
    }
}
