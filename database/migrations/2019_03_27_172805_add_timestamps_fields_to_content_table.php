<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsFieldsToContentTable extends Migration
{
    private const TABLES = ["countries", "grades", "chapters", "disciplines", "categories", "discounts"];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (array_reverse(self::TABLES) as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(["created_at", "updated_at"]);
            });
        }
    }
}
