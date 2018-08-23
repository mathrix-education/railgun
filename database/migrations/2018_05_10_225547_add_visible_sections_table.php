<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddVisibleSectionsTable.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since 4.0.0
 *
 * @copyright Mathrix Education SA
 */
class AddVisibleSectionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table("sections", function (Blueprint $table) {
            $table->boolean("visible")->after("slug");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("sections", function (Blueprint $table) {
            $table->dropColumn("visible");
        });
    }
}
