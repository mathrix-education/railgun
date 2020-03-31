<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MergePostsModules extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', static function (Blueprint $table): void {
            $table->boolean('visible')->after('slug')->nullable();
            $table->unsignedTinyInteger('order')->after('slug')->nullable();
            $table->json('content')->after('slug')->nullable();
            $table->string('video_id')->after('slug')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', static function (Blueprint $table): void {
            $table->dropColumn(['visible', 'order', 'content']);
        });
    }
}
