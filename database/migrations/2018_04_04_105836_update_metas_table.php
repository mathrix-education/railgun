<?php

use App\Models\Meta;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("metas", function(Blueprint $table) {
            $table->boolean("to_revise")->after("favorite")->default(false);
            $table->boolean("completed")->after("to_revise")->default(false);
        });

        $metas = Meta::all();

        foreach ($metas as $meta) {
            $meta->to_revise = $meta->status == "to_revise";
            $meta->completed = $meta->status == "completed";
        }

        Schema::table("metas", function(Blueprint $table) {
            $table->dropColumn("status");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("metas", function(Blueprint $table) {
            $table->enum("status", ["to_revise", "completed"])->after("favorite")->default(null);
        });

        $metas = Meta::all();

        foreach ($metas as $meta) {
            if($meta->to_revise) {
                $meta->status = "to_revise";
            } else if($meta->completed) {
                $meta->status = "completed";
            };
        }

        Schema::table("metas", function(Blueprint $table) {
            $table->dropColumn("to_revise");
            $table->dropColumn("completed");
        });
    }
}
