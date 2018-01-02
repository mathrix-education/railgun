<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateQuotesTable
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix SA
 */
class CreateQuotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("quotes");
    }
}
