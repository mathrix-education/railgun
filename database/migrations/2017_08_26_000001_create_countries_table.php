<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateCountriesTable
 *
 * @author Mathrix Back-End Team
 * @since 0.8.1
 * @copyright Mathrix
 */
class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("countries", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("name_short");
            $table->string("slug");
            $table->string("slug_short");
            $table->boolean("visible")->default(true);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("countries");
    }
}
