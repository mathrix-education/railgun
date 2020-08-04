<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FlashcardsStandard extends Migration
{
    /**
     * Run the migrations.
     *
     * @throws JsonException
     */
    public function up(): void
    {
        Schema::table('flashcards', static function (Blueprint $table): void {
            $table->dropColumn('slug');
            $table->json('content')->after('backs');
        });

        DB::table('flashcards')
            ->select(['id', 'fronts', 'backs'])
            ->get()
            ->each(static function (stdClass $data): void {
                $fronts  = json_decode($data->fronts, true, 512, JSON_THROW_ON_ERROR);
                $backs   = json_decode($data->backs, true, 512, JSON_THROW_ON_ERROR);
                $content = [];

                foreach ($fronts as $key => $front) {
                    $content[] = [
                        'front' => $front,
                        'back'  => $backs[$key],
                    ];
                }

                DB::table('flashcards')
                    ->where('id', '=', $data->id)
                    ->update(['content' => $content]);
            });

        Schema::table('flashcards', static function (Blueprint $table): void {
            $table->dropColumn(['fronts', 'backs']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @throws JsonException
     */
    public function down(): void
    {
        Schema::table('flashcards', static function (Blueprint $table): void {
            $table->json('backs')->after('name');
            $table->json('fronts')->after('name');
            $table->string('slug')->after('name')->nullable()->default(null);
        });

        DB::table('flashcards')
            ->select(['id', 'name', 'content'])
            ->get()
            ->each(static function (stdClass $data): void {
                $content = json_decode($data->content, true, 512, JSON_THROW_ON_ERROR);
                $fronts  = [];
                $backs   = [];

                foreach ($content as $flashcard) {
                    $fronts[] = $flashcard['front'];
                    $backs[]  = $flashcard['back'];
                }

                DB::table('flashcards')
                    ->where('id', '=', $data->id)
                    ->update([
                        'slug'   => Str::slug($data->name),
                        'fronts' => $fronts,
                        'backs'  => $backs,
                    ]);
            });

        Schema::table('flashcards', static function (Blueprint $table): void {
            $table->dropColumn('content');
            $table->string('slug')->nullable(false)->change();
        });
    }
}
