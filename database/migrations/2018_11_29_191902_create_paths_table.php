<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Meta;
use App\Models\Path;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function up()
    {
        Schema::create("paths", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("country_id")->unsigned();
            $table->integer("discipline_id")->unsigned();
            $table->integer("class_id")->unsigned();
            $table->integer("chapter_id")->unsigned();
            $table->integer("notion_id")->unsigned();
            $table->integer("activity_id")->unsigned();
            $table->integer("module_id")->unsigned();
            $table->boolean("seo_index")->default(false);

            $table->foreign("country_id")
                ->references("id")
                ->on("countries");
            $table->foreign("class_id")
                ->references("id")
                ->on("classes");
            $table->foreign("discipline_id")
                ->references("id")
                ->on("disciplines");
            $table->foreign("chapter_id")
                ->references("id")
                ->on("chapters");
            $table->foreign("notion_id")
                ->references("id")
                ->on("notions");
            $table->foreign("activity_id")
                ->references("id")
                ->on("activities");
            $table->foreign("module_id")
                ->references("id")
                ->on("modules");
        });

        $metas = Meta::whereNull("user_id")
            ->where("seo_index", "=", true)
            ->get();

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
            $path->seo_index = true;
            $path->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("paths", function (Blueprint $table) {
            $table->dropForeign("paths_country_id_foreign");
            $table->dropForeign("paths_class_id_foreign");
            $table->dropForeign("paths_discipline_id_foreign");
            $table->dropForeign("paths_chapter_id_foreign");
            $table->dropForeign("paths_notion_id_foreign");
            $table->dropForeign("paths_activity_id_foreign");
            $table->dropForeign("paths_module_id_foreign");
        });

        Schema::dropIfExists("paths");
    }
}
