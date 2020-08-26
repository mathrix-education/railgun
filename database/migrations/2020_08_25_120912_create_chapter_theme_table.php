<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateChapterThemeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chapter_theme', static function (Blueprint $table): void {
            $table->foreignId('theme_id')->constrained()->onDelete('cascade');
            $table->foreignId('chapter_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('order');
        });

        DB::table('chapters')
            ->selectRaw('theme_id, id as chapter_id, `order`')
            ->groupBy(['theme_id', 'chapter_id'])
            ->orderByRaw('theme_id, chapter_id')
            ->get()
            ->map(static fn(stdClass $line) => (array)$line)
            ->tap(static function(Collection $lines): void {
                DB::table('chapter_theme')->insert($lines->toArray());
            });

        Schema::table('chapters', static function(Blueprint $table): void {
            $table->dropForeign(['theme_id']);
            $table->dropColumn(['theme_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', static function(Blueprint $table): void {
            $table->unsignedSmallInteger('order');
            $table->foreignId('theme_id')->constrained();
        });

        DB::table('chapter_theme as ct1')
            ->selectRaw('theme_id, chapter_id as id, `order`')
            ->join('chapter_theme as ct2', static function($join): void {
                $join->on('ct1.chapter_id', '=', 'ct2.chapter_id');
                $join->on('ct1.theme_id', '<', 'ct2.theme_id');
            })
            ->get()
            ->tap(static function(Collection $line): void {
                DB::table('chapters')
                    ->where('id', '=', $line['id'])
                    ->update([
                        'theme_id' => $line['theme_id'],
                        'order' => $line['order'],
                    ]);
            });

        Schema::dropIfExists('chapter_theme');
    }
}
