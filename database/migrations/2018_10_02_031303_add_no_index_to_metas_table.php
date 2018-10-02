<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNoIndexToMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE metas MODIFY user_id INT(11) UNSIGNED DEFAULT NULL");

        Schema::table("metas", function (Blueprint $table) {
            $table->boolean("no_index")
                ->nullable()
                ->default(null)
                ->after("completed");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("metas", function (Blueprint $table) {
            $table->dropColumn("no_index");
        });

        DB::statement("ALTER TABLE metas MODIFY user_id INT(11) UNSIGNED NOT NULL");
    }
}
