<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateReferencesTable
 */
class CreateReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("references", function (Blueprint $table) {
            $table->increments("id");
            $table->string("key");
            $table->text("text");
            $table->integer("module_id")->unsigned();

            // Foreign keys
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
        Schema::table("references", function (Blueprint $table) {
            $table->dropForeign("references_module_id_foreign");
        });
        Schema::dropIfExists("references");
    }
}
