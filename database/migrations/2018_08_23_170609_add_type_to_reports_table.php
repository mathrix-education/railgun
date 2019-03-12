<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddTypeToReportsTable
 *
 * @author Mathrix Back-End Team
 * @since 4.1.0
 * @copyright Mathrix SA
 */
class AddTypeToReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("reports", function (Blueprint $table) {
            $table->string("type")->after("id");
            $table->string("context")->nullable()->after("id");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("reports", function (Blueprint $table) {
            $table->dropColumn("type");
            $table->dropColumn("context");
        });
    }
}
