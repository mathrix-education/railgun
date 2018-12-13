<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class UpdateMetasTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.1
 *
 * @copyright Mathrix Education SA
 */
class UpdateMetasTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table("metas", function (Blueprint $table) {
            $table->boolean("to_revise")
                ->after("favorite")
                ->default(false);
            $table->boolean("completed")
                ->after("to_revise")
                ->default(false);
            $table->dateTime("created_at");
            $table->dateTime("updated_at");
        });

        $metas = DB::table("metas")->select()->get();

        foreach ($metas as $meta) {
            DB::table("metas")->where("id", "=", $meta->id)->update([
                "to_revise" => $meta->status === "to_revise",
                "completed" => $meta->status === "completed"
            ]);
        }

        Schema::table("metas", function (Blueprint $table) {
            $table->dropColumn("status");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("metas", function (Blueprint $table) {
            $table->enum("status", ["to_revise", "completed"])
                ->after("favorite")
                ->nullable()
                ->default(null);
        });

        $metas = DB::table("metas")->select()->get();

        foreach ($metas as $meta) {
            DB::table("metas")->where("id", "=", $meta->id)->update([
                "status" => $meta->to_revise ? "to_revise" : ($meta->completed ? "completed" : null)
            ]);
        }

        Schema::table("metas", function (Blueprint $table) {
            $table->dropColumn("to_revise");
            $table->dropColumn("completed");
            $table->dropColumn("created_at");
            $table->dropColumn("updated_at");
        });
    }
}
