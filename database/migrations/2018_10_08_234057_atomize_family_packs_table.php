<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AtomizeFamilyPacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("family_packs", function (Blueprint $table) {
            $table->dropForeign("family_packs_child_id_foreign");
        });

        Schema::table("family_packs", function (Blueprint $table) {
            $table->dropColumn("child_id");
            $table->integer("children_count")->after("id");
            $table->string("braintree_id")->after("id");
        });

        Schema::table("users", function (Blueprint $table) {
            $table->integer("family_pack_id")
                ->unsigned()
                ->nullable()
                ->after("class_id");

            // Foreign keys
            $table->foreign("family_pack_id")
                ->references("id")
                ->on("family_packs");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropForeign("users_family_pack_id_foreign");

            // Foreign keys
            $table->dropColumn("family_pack_id");
        });

        Schema::table("family_packs", function (Blueprint $table) {
            $table->dropColumn("children_count");
            $table->dropColumn("braintree_id");

            $table->integer("child_id")
                ->after("parent_id")
                ->nullable()// Care of not-null child_id
                ->unsigned();
            $table->foreign("child_id")
                ->references("id")
                ->on("users");
        });
    }
}
