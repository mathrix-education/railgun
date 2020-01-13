<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;
use function implode;

class FlashcardsCommand extends BaseCommand
{
    protected $signature = 'v7:flashcards';

    public function handle(): void
    {
        $this->info('Migrating flashcards');
        $table           = DB::table('modules');
        $flashcardsQuery = $table->where('modules.type', '=', 'flashcard');
        $count           = $flashcardsQuery->count();
        $this->line("Found $count flashcard modules");

        $this->info('Converting flashcards');
        /*
         * With this query, we already got the flashcards with the right fields are ordered by continuous chapters.
         */
        $flashcards = $flashcardsQuery
            ->join('activities', 'modules.activity_id', '=', 'activities.id')
            ->join('notions', 'activities.notion_id', '=', 'notions.id')
            ->selectRaw(implode(',', [
                'questions AS fronts',
                'corrections AS backs',
                'modules.visible AS visible',
                'notions.chapter_id AS chapter_id',
                'modules.created_at AS created_at',
                'modules.updated_at AS updated_at',
                'CONCAT(\'module-\', modules.id) AS mapping',
            ]))
            ->orderByRaw('notions.chapter_id, notions.order, activities.order, modules.order')
            ->get()
            ->toArray();

        // Patch orders
        $iterator  = 0;
        $chapterId = null;
        foreach ($flashcards as $key => $flashcard) {
            if ($flashcard->chapter_id !== $chapterId) {
                $iterator = 0;
            }

            $flashcard->order = $iterator;

            $flashcards[$key] = (array)$flashcard; // Convert to array

            // Update iterator
            $chapterId = $flashcard->chapter_id;
            $iterator++;
        }

        DB::table('flashcards')->insert($flashcards);
    }
}
