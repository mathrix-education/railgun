<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateDisciplinesTable
 *
 * @author Mathrix Back-End Team
 * @since 0.8.1
 * @copyright Mathrix
 */
class CreateDisciplinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("disciplines", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("slug");
            $table->boolean("beta")->default(0);
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
        Schema::dropIfExists("disciplines");
    }
}
