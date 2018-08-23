<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateQuotesTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateQuotesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("quotes", function (Blueprint $table) {
            $table->increments("id");
            $table->text("content");
            $table->string("author");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists("quotes");
    }
}
