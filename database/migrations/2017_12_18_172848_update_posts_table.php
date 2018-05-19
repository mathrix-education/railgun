<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Module;
use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class UpdatePostsTable
 *
 * @author Mathrix Back-End Team
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class UpdatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws Exception
     */
    public function up()
    {
        Schema::table("posts", function (Blueprint $table) {
            $table->integer("module_id")->unsigned()->after("slug")->nullable();

            // Foreign
            $table->foreign("module_id")->references("id")->on("modules");
        });

        $posts = Post::all();

        foreach ($posts as $post) {
            $module = new Module([
                "premium" => false,
                "type" => "course",
                "content" => $post->content,
                "questions" => null,
                "choices" => null,
                "solutions" => null,
                "corrections" => null,
                "options" => null,
                "visible" => true
            ]);
            $module->save();

            $post->update(["module_id" => $module->id]);
        }

        Schema::table("posts", function (Blueprint $table) {
            $table->dropColumn("content");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function down()
    {
        Schema::table("posts", function (Blueprint $table) {
            $table->text("content")->after("slug");
            $table->dropForeign("posts_module_id_foreign");
        });

        $posts = Post::all();
        $modulesToDelete = [];

        foreach ($posts as $post) {
            $post->unguardAttributes(["content"]);
            $post->update(["content" => $post->module->content]);

            $modulesToDelete[] = $post->module->id;
        }

        //Module::whereIn("id", $modulesToDelete)->delete();

        Schema::table("posts", function (Blueprint $table) {
            $table->dropColumn("module_id");
        });
    }
}
