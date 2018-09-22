<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Notion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateChapterNotionTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateChapterNotionTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("chapter_notion", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("chapter_id")
                ->unsigned();
            $table->integer("notion_id")
                ->unsigned();
            $table->integer("order")
                ->default(0);

            // Foreign keys
            $table->foreign("chapter_id")
                ->references("id")
                ->on("chapters");
            $table->foreign("notion_id")
                ->references("id")
                ->on("notions");
        });

        $notions = Notion::all();
        /** @var Notion $notion */
        foreach ($notions as $notion) {
            $notion->chapters()
                ->attach([$notion->chapter_id => ["order" => $notion->order]]);
        }

        Schema::table("notions", function (Blueprint $table) {
            $table->dropForeign("notions_chapter_id_foreign");

            $table->dropColumn("chapter_id");
            $table->dropColumn("order");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @throws Exception
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function down()
    {
        Schema::table("notions", function (Blueprint $table) {
            $table->integer("chapter_id")
                ->unsigned()
                ->nullable();
            $table->integer("order")
                ->default(0);

            $table->foreign("chapter_id")
                ->references("id")
                ->on("chapters");
        });

        $notions = Notion::all();
        /** @var Notion $notion */
        foreach ($notions as $notion) {
            $chapter = $notion->chapters()
                ->first();

            if ($chapter !== null) {
                $notion->chapter_id = $chapter->id;
                $notion->order = $chapter->pivot->order;
                $notion->save();
            }
        }

        Schema::table("chapter_notion", function (Blueprint $table) {
            $table->dropForeign("chapter_notion_chapter_id_foreign");
            $table->dropForeign("chapter_notion_notion_id_foreign");
        });

        Schema::dropIfExists("chapter_notion");
    }
}
