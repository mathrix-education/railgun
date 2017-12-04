<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateActivityModuleTable
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix SA
 */
class CreateActivityModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("activity_module", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("activity_id")->unsigned();
            $table->integer("module_id")->unsigned();
            $table->integer("order")->default(0);

            // Foreign keys
            $table->foreign("activity_id")->references("id")->on("activities");
            $table->foreign("module_id")->references("id")->on("modules");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("activity_module", function (Blueprint $table) {
            $table->dropForeign("activity_module_activity_id_foreign");
            $table->dropForeign("activity_module_module_id_foreign");
        });

        Schema::dropIfExists("activity_module");
    }
}
