<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class UpdateUsersTable
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dateTime("created_at");
            $table->dateTime("updated_at");
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
            $table->dropColumn("created_at");
            $table->dropColumn("updated_at");
        });
    }
}
