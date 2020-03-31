<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function array_merge;
use function collect;
use function floor;
use function range;
use function uuid_create;

/**
 * Extract sessions from interactions
 */
class SessionsCommand extends V7Command
{
    private const PAGE_SIZE = 1000;

    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:sessions';

    public function handle(): void
    {
        $this->title('Extracting sessions from interactions');

        $this->migrateTo('2020_03_22_181818_create_sessions_table');

        $max = DB::table('users')->max('id');

        collect(range(0, floor($max / self::PAGE_SIZE)))
            ->tap(function () use ($max): void {
                $this->line('Building sessions');
                $this->getOutput()->progressStart($max);
            })
            ->map(function (int $page) {
                return $this->getUnionForPage($page)
                    ->get()
                    ->sortBy('updated_at')
                    ->mapWithKeys(static fn($line) => ["{$line->user_id}.{$line->discipline_id}" => $line])
                    ->values()
                    ->map(static fn($line) => (array)$line)
                    ->tap(fn(Collection $lines) => $this->getOutput()->progressAdvance($lines->count()));
            })
            ->tap(fn() => $this->getOutput()->progressFinish())
            ->flatten(1)
            ->tap(function (Collection $lines): void {
                $this->line('Saving sessions');
                $this->getOutput()->progressStart($lines->count());
            })
            ->map(static fn($s) => array_merge($s, ['id' => uuid_create()]))
            ->chunk(self::PAGE_SIZE)
            ->each(function (Collection $chunk): void {
                DB::table('sessions')->insert($chunk->toArray());
                $this->getOutput()->progressAdvance($chunk->count());
            })
            ->tap(fn(Collection $chunk) => $this->getOutput()->progressAdvance($chunk->count()))
            ->tap(fn(Collection $chunk) => $this->getOutput()->newLine());
    }

    private function getCoursesBuilder(): Builder
    {
        return DB::table('interactions', 'i')
            ->select(['user_id', 'discipline_id', 'chapter_id', 'i.updated_at'])
            ->join(DB::raw('courses cs'), static function (JoinClause $join): void {
                $join->on('i.interactable_id', '=', 'cs.id')
                    ->where('i.interactable_type', '=', 'course');
            })
            ->join(DB::raw('notions n'), 'cs.notion_id', '=', 'n.id')
            ->join(DB::raw('chapters c'), 'n.chapter_id', '=', 'c.id')
            ->join(DB::raw('users u'), static function (JoinClause $join): void {
                $join->on('i.user_id', '=', 'u.id')
                    ->on('u.grade_id', '=', 'c.grade_id');
            });
    }

    private function getSubjectsBuilder(): Builder
    {
        return DB::table('interactions', 'i')
            ->select(['user_id', 'discipline_id', 'chapter_id', 'i.updated_at'])
            ->join(DB::raw('subjects s'), static function (JoinClause $join): void {
                $join->on('i.interactable_id', '=', 's.id')
                    ->where('i.interactable_type', '=', 'subject');
            })
            ->join(DB::raw('notions n'), 's.notion_id', '=', 'n.id')
            ->join(DB::raw('chapters c'), 'n.chapter_id', '=', 'c.id')
            ->join(DB::raw('users u'), static function (JoinClause $join): void {
                $join->on('i.user_id', '=', 'u.id')
                    ->on('u.grade_id', '=', 'c.grade_id');
            });
    }

    private function getExercisesBuilder(): Builder
    {
        return DB::table('interactions', 'i')
            ->select(['user_id', 'discipline_id', 'chapter_id', 'i.updated_at'])
            ->join(DB::raw('exercises e'), static function (JoinClause $join): void {
                $join->on('i.interactable_id', '=', 'e.id')
                    ->where('i.interactable_type', '=', 'exercise');
            })
            ->join(DB::raw('subjects s'), 'e.subject_id', '=', 's.id')
            ->join(DB::raw('notions n'), 's.notion_id', '=', 'n.id')
            ->join(DB::raw('chapters c'), 'n.chapter_id', '=', 'c.id')
            ->join(DB::raw('users u'), static function (JoinClause $join): void {
                $join->on('i.user_id', '=', 'u.id')
                    ->on('u.grade_id', '=', 'c.grade_id');
            });
    }

    private function getFlashcardBuilder(): Builder
    {
        return DB::table('interactions', 'i')
            ->select(['user_id', 'discipline_id', 'chapter_id', 'i.updated_at'])
            ->join(DB::raw('flashcards f'), static function (JoinClause $join): void {
                $join->on('i.interactable_id', '=', 'f.id')
                    ->where('i.interactable_type', '=', 'flashcard');
            })
            ->join(DB::raw('chapters c'), 'f.chapter_id', '=', 'c.id')
            ->join(DB::raw('users u'), static function (JoinClause $join): void {
                $join->on('i.user_id', '=', 'u.id')
                    ->on('u.grade_id', '=', 'c.grade_id');
            });
    }

    private function getExamsBuilder(): Builder
    {
        return DB::table('interactions', 'i')
            ->select(['user_id', 'discipline_id', 'chapter_id', 'i.updated_at'])
            ->join(DB::raw('exams e'), static function (JoinClause $join): void {
                $join->on('i.interactable_id', '=', 'e.id')
                    ->where('i.interactable_type', '=', 'exam');
            })
            ->join(DB::raw('chapters c'), 'e.chapter_id', '=', 'c.id')
            ->join(DB::raw('users u'), static function (JoinClause $join): void {
                $join->on('i.user_id', '=', 'u.id')
                    ->on('u.grade_id', '=', 'c.grade_id');
            });
    }

    private function getUnionForPage(int $page): Builder
    {
        $min = $page * self::PAGE_SIZE;
        $max = ($page + 1) * self::PAGE_SIZE - 1;

        $courses    = $this->getCoursesBuilder()->whereBetween('user_id', [$min, $max]);
        $subjects   = $this->getSubjectsBuilder()->whereBetween('user_id', [$min, $max]);
        $exercises  = $this->getExercisesBuilder()->whereBetween('user_id', [$min, $max]);
        $flashcards = $this->getFlashcardBuilder()->whereBetween('user_id', [$min, $max]);
        $exams      = $this->getExamsBuilder()->whereBetween('user_id', [$min, $max]);

        return $courses->union($subjects)->union($exercises)->union($flashcards)->union($exams);
    }
}
