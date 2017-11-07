<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateClassesTable
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix
 */
class CreateClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("classes", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("name_short");
            $table->string("slug");
            $table->string("slug_short");
            $table->integer("order")->default(0);
            $table->boolean("visible")->default(true);
            $table->integer("parent_id")->unsigned()->nullable();
            $table->integer("country_id")->unsigned();

            // Foreign keys
            $table->foreign("parent_id")->references("id")->on("classes");
            $table->foreign("country_id")->references("id")->on("countries");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("classes", function (Blueprint $table) {
            $table->dropForeign("classes_parent_id_foreign");
            $table->dropForeign("classes_country_id_foreign");
        });

        Schema::dropIfExists("classes");
    }
}
