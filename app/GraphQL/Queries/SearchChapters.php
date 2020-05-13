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
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The query arguments (query, grade_id).
     *
     * @return Builder<Chapter>
     */
    public function __invoke($rootValue, array $args): Builder
    {
        $query    = $args['query'];
        $like     = "%{$query}%";
        $graph    = new GradeWeights();
        $weights1 = $graph->getSql($args['grade_id'], '`chapters`.`grade_id`');
        $weights2 = $graph->getSql($args['grade_id'], '`chapters2`.`grade_id`');

        return Chapter::query()
            ->selectRaw("`chapters`.*")
            ->leftJoin(DB::raw("`chapters` as chapters2"), static function (JoinClause $join) use (
                $weights1,
                $weights2
            ): void {
                $join->on('chapters.slug', '=', 'chapters2.slug')
                    ->whereRaw("$weights1 > $weights2");
            })
            ->whereNull('chapters2.id')
            ->where(static function (Builder $query) use ($like): void {
                $query->orWhere('chapters.name', 'LIKE', $like)
                    ->orWhere('chapters.slug', 'LIKE', $like);
            });
    }
}
