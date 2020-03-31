<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Railgun\Buster\Pipelines\CoursePipeline;
use Railgun\Buster\Utils\SetOrder;
use stdClass;
use function implode;

/**
 * Migrate modules => courses
 */
class CoursesCommand extends V7Command
{
    private const CHUNK_SIZE = 100;

    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:courses';
    /** @var CoursePipeline $pipeline The course pipeline. */
    private CoursePipeline $pipeline;

    public function handle(CoursePipeline $pipeline): void
    {
        $this->pipeline = $pipeline;
        $this->title('Migrating modules => courses');

        DB::table('modules')
            ->where('modules.type', '=', 'course')
            ->where('modules.content', '!=', '')
            ->join('activities', static function (JoinClause $join): void {
                $join->on('modules.activity_id', '=', 'activities.id')
                    ->where('activities.notion_id', '>', 0);
            })
            ->join('notions', static function (JoinClause $join): void {
                // Exclude video tests
                $join->on('activities.notion_id', '=', 'notions.id')
                    ->where('notions.name', 'NOT LIKE', '%erroga%');
            })
            ->selectRaw(implode(',', [
                'modules.id AS id', // keep module ids, for SEO compatibility (mapping is not necessary)
                'activities.name AS name',
                "JSON_EXTRACT(modules.content, '$.silicate') AS content",
                'activities.premium AS premium',
                'modules.visible AS visible',
                'modules.seo_index AS seo_index',
                'modules.seo_title AS seo_title',
                'modules.seo_slug AS seo_slug',
                'modules.seo_description AS seo_description',
                'modules.seo_keywords AS seo_keywords',
                'activities.notion_id AS notion_id',
                'modules.created_at AS created_at',
                'modules.updated_at AS updated_at',
                "CONCAT('module-', modules.id) AS mapping",
            ]))
            ->get()
            ->tap(fn(Collection $courses) => $this->line("Found {$courses->count()} courses"))
            ->map(fn(stdClass $course) => $this->pipeline->send($course))
            ->reject(static fn($course) => $course === false)
            ->pipe(new SetOrder('notion_id'))
            ->tap(fn(Collection $courses) => $this->getOutput()->progressStart($courses->count()))
            ->chunk(self::CHUNK_SIZE)
            ->each(function (Collection $courses): void {
                DB::table('courses')->insert($courses->toArray());
                $this->getOutput()->progressAdvance($courses->count());
            })
            ->tap(fn() => $this->getOutput()->progressFinish());
    }
}
