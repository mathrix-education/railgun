<?php

use App\Exceptions\Http422ModelSaveException;
use App\Models\Meta;
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
     * @throws Http422ModelSaveException
     */
    public function down()
    {
        Schema::table("metas", function (Blueprint $table) {
            $table->dropColumn("no_index");
        });

        Meta::whereNull("user_id")->delete();
        //DB::statement("ALTER TABLE metas MODIFY user_id int(11) unsigned NOT NULL;");
    }
}
