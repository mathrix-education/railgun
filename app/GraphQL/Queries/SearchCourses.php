<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Railgun\Models\Course;
use Railgun\Support\GradeWeights;

/**
 * Query searchCourses.
 * Search courses using a query string.
 */
class SearchCourses
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The query arguments (query, grade_id).
     *
     * @return Builder<Course>
     */
    public function __invoke($_, array $args): Builder
    {
        $query    = $args['query'];
        $like     = "%{$query}%";
        $graph    = new GradeWeights();
        $weights1 = $graph->getSql($args['grade_id'], '`themes`.`grade_id`');
        $weights2 = $graph->getSql($args['grade_id'], '`themes_inner`.`grade_id`');

        return Course::query()
            ->selectRaw('`courses`.*')
            ->join('notions', 'notions.id', '=', 'courses.notion_id')
            ->join('chapters', 'chapters.id', '=', 'notions.chapter_id')
            ->join('themes', 'chapters.theme_id', '=', 'themes.id')
            ->join(DB::raw('`chapters` as chapters_inner'), 'chapters.slug', '=', 'chapters_inner.slug')
            ->join(DB::raw('`themes` as themes_inner'), 'chapters_inner.theme_id', '=', 'themes_inner.id')
            ->leftJoin(DB::raw("`chapters` as chapter_compare"), static function (JoinClause $join) use (
                $weights1,
                $weights2
            ): void {
                $join->on('chapters.slug', '=', 'chapter_compare.slug')
                    ->whereRaw("$weights1 > $weights2");
            })
            ->whereNull('chapter_compare.id')
            ->where(static function (Builder $query) use ($like): void {
                $query->orWhere('courses.name', 'LIKE', $like)
                    ->orWhere('courses.slug', 'LIKE', $like)
                    ->orWhere('courses.seo_title', 'LIKE', $like)
                    ->orWhere('courses.seo_keywords', 'LIKE', $like)
                    ->orWhere('courses.seo_description', 'LIKE', $like)
                    ->orWhere('courses.content', 'LIKE', $like);
            })
            ->where('chapters.visible', '=', true)
            ->where('courses.visible', '=', true);
    }
}
