<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateFamilyPacksTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class CreateFamilyPacksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("family_packs", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("parent_id")
                ->unsigned();
            $table->integer("child_id")
                ->unsigned();
            $table->timestamps();

            // Foreign keys
            $table->foreign("parent_id")
                ->references("id")
                ->on("users");
            $table->foreign("child_id")
                ->references("id")
                ->on("users");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("family_packs", function (Blueprint $table) {
            $table->dropForeign("family_packs_parent_id_foreign");
            $table->dropForeign("family_packs_child_id_foreign");
        });

        Schema::dropIfExists("family_packs");
    }
}
