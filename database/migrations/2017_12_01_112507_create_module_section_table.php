<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateModuleSectionTable
 *
 * @author Mathrix Back-End Team
 * @since 1.0.1
 * @copyright Mathrix SA
 */
class CreateModuleSectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_section', function (Blueprint $table) {
            $table->increments("id");
            $table->integer("module_id")->unsigned();
            $table->integer("section_id")->unsigned();
            $table->integer("order")->default(0);

            // Foreign keys
            $table->foreign("module_id")->references("id")->on("modules");
            $table->foreign("section_id")->references("id")->on("sections");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("module_section", function (Blueprint $table) {
            $table->dropForeign("module_section_module_id_foreign");
            $table->dropForeign("module_section_section_id_foreign");
        });

        Schema::dropIfExists('module_section');
    }
}
