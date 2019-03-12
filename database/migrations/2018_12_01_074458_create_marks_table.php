<?php

use App\Models\Path;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("marks", function (Blueprint $table) {
            $table->increments("id");
            $table->string("status");
            $table->integer("user_id")->unsigned();
            $table->integer("path_id")->unsigned();
            $table->timestamps();

            $table->foreign("user_id")->references("id")->on("users");
            $table->foreign("path_id")->references("id")->on("paths");
        });

        // Migrate existing metas
        $metas = DB::table("metas")
            ->whereNotNull("user_id")
            ->whereNotNull("country_id")
            ->whereNotNull("class_id")
            ->whereNotNull("discipline_id")
            ->whereNotNull("chapter_id")
            ->whereNotNull("notion_id")
            ->whereNotNull("activity_id")
            ->whereNotNull("module_id")
            ->get();

        $buffer = [];

        foreach ($metas as $meta) {
            $path = Path::retrieve([
                "country_id" => $meta->country_id,
                "class_id" => $meta->class_id,
                "discipline_id" => $meta->discipline_id,
                "chapter_id" => $meta->chapter_id,
                "notion_id" => $meta->notion_id,
                "activity_id" => $meta->activity_id,
                "module_id" => $meta->module_id
            ]);

            $status = $meta->completed ? "completed" : ($meta->to_revise ? "to_revise" : "view");

            $createdAt = $meta->created_at !== "0000-00-00 00:00:00" ? $meta->created_at : \Carbon\Carbon::now();
            $updatedAt = $meta->created_at !== "0000-00-00 00:00:00" ? $meta->updated_at : \Carbon\Carbon::now();

            $buffer[] = [
                "user_id" => $meta->user_id,
                "path_id" => $path->id,
                "status" => $status,
                "created_at" => $createdAt,
                "updated_at" => $updatedAt
            ];

            if (count($buffer) === 1000) {
                DB::table("marks")->insert($buffer);
                $buffer = [];
            }
        }

        DB::table("marks")->insert($buffer);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("marks", function (Blueprint $table) {
            $table->dropForeign("marks_user_id_foreign");
            $table->dropForeign("marks_path_id_foreign");
        });

        Schema::dropIfExists("marks");
    }
}
