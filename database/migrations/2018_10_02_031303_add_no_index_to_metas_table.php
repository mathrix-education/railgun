<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        DB::table("metas")->whereNull("user_id")->delete();
        //DB::statement("ALTER TABLE metas MODIFY user_id int(11) unsigned NOT NULL;");
    }
}
