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
            $table->foreignId('parent_id')
                ->constrained()
                ->onDelete('cascade');

            $table->dropForeign('comments_user_id_foreign');
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
        });

        Schema::table('votes', static function (Blueprint $table): void {
            $table->dropForeign('votes_comment_id_foreign');
            $table->foreignId('comment_id')
                ->constrained()
                ->onDelete('cascade');

            $table->dropForeign('votes_user_id_foreign');
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // It is not possible to revert this one
    }
}
