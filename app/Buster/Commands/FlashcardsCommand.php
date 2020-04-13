<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Railgun\Buster\Pipelines\FlashcardPipeline;
use Railgun\Buster\Utils\SetOrder;
use stdClass;
use function implode;

/**
 * Migrate flashcard modules => flashcards
 */
class FlashcardsCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:flashcards';
    /** @var FlashcardPipeline The flashcard pipeline. */
    protected FlashcardPipeline $pipeline;

    public function handle(FlashcardPipeline $pipeline): void
    {
        $this->pipeline = $pipeline;
        $this->title('Migrating modules => flashcards');

        /*
         * With this query, we already got the flashcards with the right fields are ordered by continuous chapters.
         */
        DB::table('modules')
            ->where('modules.type', '=', 'flashcard')
            ->join('activities', 'modules.activity_id', '=', 'activities.id')
            ->join('notions', 'activities.notion_id', '=', 'notions.id')
            ->selectRaw(implode(',', [
                'activities.name AS name',
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
            ->reject(static fn(stdClass $flashcard) => $flashcard->fronts === '""' || $flashcard->backs === '""')
            ->map(fn(stdClass $flashcard) => $this->pipeline->send($flashcard))
            ->reject(static fn($flashcard) => $flashcard === false)
            ->pipe(new SetOrder('chapter_id'))
            ->tap(static fn(Collection $data) => DB::table('flashcards')->insert($data->toArray()));
    }
}
