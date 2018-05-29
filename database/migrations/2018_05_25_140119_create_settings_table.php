<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;


/**
 * Class CreateSettingsTable
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("settings", function (Blueprint $table) {
            $table->increments("id");
            $table->string("key")->index();
            $table->text("value");
            $table->boolean("visible")->default(1);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("settings");
    }
}
