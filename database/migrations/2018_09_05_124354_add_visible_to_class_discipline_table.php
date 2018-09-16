<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisibleToClassDisciplineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('class_discipline', function (Blueprint $table) {
            $table->boolean("visible")->default(true);
            $table->integer("order");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('class_discipline', function (Blueprint $table) {
            $table->dropColumn("visible");
            $table->dropColumn("order");
        });
    }
}
