<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPathIdToComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("comments", function (Blueprint $table) {
            $table->integer("path_id")
                ->unsigned()
                ->nullable()
                ->default(null)
                ->after("user_id");
            $table->integer("module_id")
                ->unsigned()
                ->after("user_id");

            $table->foreign("path_id")->references("id")->on("paths");
        });

        $comments = \App\Models\Comment::withTrashed()->get();

        foreach ($comments as $comment) {
            if ($comment->commentable_type == "post") {
                $post = \App\Models\Post::findOrFail($comment->commentable_id);
                $comment->module_id = $post->module->id;
            } elseif ($comment->commentable_type == "activity") {
                $activity = \App\Models\Activity::findOrFail($comment->commentable_id);

                if ($activity->modules()->get()->isEmpty()) {
                    $comment->forceDelete();
                    continue;
                }

                $comment->module_id = $activity->modules[0]->id;
            } elseif ($comment->commentable_type == "module") {
                $comment->module_id = $comment->commentable_id;
            } else {
                $comment->forceDelete();
                continue;
            }

            $comment->save();
        }

        Schema::table("comments", function (Blueprint $table) {
            $table->foreign("module_id")->references("id")->on("modules");

            $table->dropColumn("commentable_id");
            $table->dropColumn("commentable_type");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("comments", function (Blueprint $table) {
            $table->morphs("commentable");
            $table->dropColumn("path_id");
            $table->dropColumn("module_id");
        });
    }
}
