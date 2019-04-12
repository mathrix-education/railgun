<?php

use App\Models\Notion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateNotionsTable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class CreateNotionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create("notions", function (Blueprint $table) {
            $table->increments("id");
            $table->string("name");
            $table->string("slug");
            $table->enum("type", Notion::TYPES);
            $table->integer("order")
                ->default(0);
            $table->boolean("visible")
                ->default(true);
            $table->integer("chapter_id")
                ->unsigned();

            // Foreign keys
            $table->foreign("chapter_id")
                ->references("id")
                ->on("chapters");
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table("notions", function (Blueprint $table) {
            $table->dropForeign("notions_chapter_id_foreign");
        });

        Schema::dropIfExists("notions");
    }
}
