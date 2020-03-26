<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Railgun\MigrationV7\Silicate\FlashcardConverter;
use stdClass;
use const JSON_THROW_ON_ERROR;
use function implode;
use function json_encode;

class FlashcardsCommand extends V7Command
{
    protected $signature = 'v7:flashcards';

    public function handle(): void
    {
        $this->title('Migrating modules => flashcards');

        $iterator  = 0;
        $chapterId = null;

        /*
         * With this query, we already got the flashcards with the right fields are ordered by continuous chapters.
         */
        DB::table('modules')
            ->where('modules.type', '=', 'flashcard')
            ->join('activities', 'modules.activity_id', '=', 'activities.id')
            ->join('notions', 'activities.notion_id', '=', 'notions.id')
            ->selectRaw(implode(',', [
                'questions AS fronts',
                'corrections AS backs',
                'activities.premium AS premium',
                'notions.visible && activities.visible && modules.visible AS visible',
                'notions.chapter_id AS chapter_id',
                'modules.created_at AS created_at',
                'modules.updated_at AS updated_at',
                "CONCAT('module-', modules.id) AS mapping",
            ]))
            ->orderByRaw('notions.chapter_id, notions.order, activities.order, modules.order')
            ->get()
            ->tap(fn(Collection $data) => $this->line("Found {$data->count()} flashcard modules"))
            ->reject(fn(stdClass $flashcard) => $flashcard->fronts === '""' || $flashcard->backs === '""')
            ->map(static function (stdClass $flashcard) use (&$iterator, &$chapterId) {
                // Convert fronts and backs to Silicate format
                $flashcard         = FlashcardConverter::new()->convert($flashcard);
                $flashcard->fronts = json_encode($flashcard->fronts, JSON_THROW_ON_ERROR, 512);
                $flashcard->backs  = json_encode($flashcard->backs, JSON_THROW_ON_ERROR, 512);

                // Set order
                if ($flashcard->chapter_id !== $chapterId) {
                    // Chapter id has changed
                    $iterator  = 0;
                    $chapterId = $flashcard->chapter_id;
                }

                $flashcard->order = $iterator;
                $iterator++;

                return (array)$flashcard; // Cast stdClass to array
            })
            ->tap(fn(Collection $data) => DB::table('flashcards')->insert($data->toArray()));
    }
}
