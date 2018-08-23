<?php

use App\Models\Meta;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class UpdateMetasTable.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since 1.0.1
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
            $table->boolean("to_revise")->after("favorite")->default(false);
            $table->boolean("completed")->after("to_revise")->default(false);
            $table->dateTime("created_at");
            $table->dateTime("updated_at");
        });

        $metas = Meta::all();

        foreach ($metas as $meta) {
            $meta->to_revise = $meta->status === "to_revise";
            $meta->completed = $meta->status === "completed";
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
            $table->enum("status", ["to_revise", "completed"])->after("favorite")->default(null);
        });

        $metas = Meta::all();

        foreach ($metas as $meta) {
            if ($meta->to_revise) {
                $meta->status = "to_revise";
            } elseif ($meta->completed) {
                $meta->status = "completed";
            }
        }

        Schema::table("metas", function (Blueprint $table) {
            $table->dropColumn("to_revise");
            $table->dropColumn("completed");
            $table->dropColumn("created_at");
            $table->dropColumn("updated_at");
        });
    }
}
