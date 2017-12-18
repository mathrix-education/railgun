<?php

use App\Models\Module;
use App\Models\Post;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws \App\Exceptions\Http422ModelSaveException
     * @throws \App\Exceptions\Http422ModelValidationException
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
     */
    public function down()
    {

        Schema::table("posts", function (Blueprint $table) {
            $table->text("content")->after("slug");
            $table->dropForeign("posts_module_id_foreign");
        });

        $posts = Post::all();

        foreach ($posts as $post) {
            $post->unguardAttributes(["content"]);
            $post->update(["content" => $post->module->content]);

            $post->module->delete();
        }

        Schema::table("posts", function (Blueprint $table) {
            $table->dropColumn("module_id");
        });
    }
}
