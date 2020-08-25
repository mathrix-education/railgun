<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ForeignKeysCascade extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', static function (Blueprint $table): void {
            $table->dropForeign('comments_parent_id_foreign');
            $table->foreign('parent_id')
                ->references('id')
                ->on('comments')
                ->onDelete('cascade');

            $table->dropForeign('comments_user_id_foreign');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::table('votes', static function (Blueprint $table): void {
            $table->dropForeign('votes_comment_id_foreign');
            $table->foreign('comment_id')
                ->references('id')
                ->on('comments')
                ->onDelete('cascade');

            $table->dropForeign('votes_user_id_foreign');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', static function (Blueprint $table): void {
            $table->dropForeign('comments_parent_id_foreign');
            $table->foreign('parent_id')
                ->references('id')
                ->on('comments');

            $table->dropForeign('comments_user_id_foreign');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::table('votes', static function (Blueprint $table): void {
            $table->dropForeign('votes_comment_id_foreign');
            $table->foreign('comment_id')
                ->references('id')
                ->on('comments');

            $table->dropForeign('votes_user_id_foreign');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }
}
