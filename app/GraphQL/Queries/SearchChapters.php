<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Railgun\Models\Chapter;
use Railgun\Support\GradeWeights;

/**
 * Query searchChapters.
 * Search chapters using a query string.
 */
class SearchChapters
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The query arguments (query, grade_id).
     *
     * @return Builder<Chapter>
     */
    public function __invoke($_, array $args): Builder
    {
        $query    = $args['query'];
        $like     = "%{$query}%";
        $graph    = new GradeWeights();
        $weights1 = $graph->getSql($args['grade_id'], '`themes`.`grade_id`');
        $weights2 = $graph->getSql($args['grade_id'], '`themes_inner`.`grade_id`');

        return Chapter::query()
            ->selectRaw("`chapters`.*")
            ->join('themes', 'chapters.theme_id', '=', 'themes.id')
            ->crossJoin(DB::raw('`chapters` as chapters_inner'), 'chapters.slug', '=', 'chapters_inner.slug')
            ->join(DB::raw('`themes` as themes_inner'), 'chapters_inner.theme_id', '=', 'themes_inner.id')
            ->join(DB::raw("`chapters` as chapter_compare"), static function (JoinClause $join) use (
                $weights1,
                $weights2
            ): void {
                $join->on('chapters.slug', '=', 'chapter_compare.slug')
                    ->whereRaw("$weights1 < $weights2");
            })
            ->where(static function (Builder $query) use ($like): void {
                $query->orWhere('chapters.name', 'LIKE', $like)
                    ->orWhere('chapters.slug', 'LIKE', $like);
            })
            ->where('chapters.visible', '=', true)
            ->groupBy('chapters.id');
    }
}
