<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Railgun\Models\Chapter;
use Railgun\Models\Course;

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
        $query = $args['query'];
        $like  = "%{$query}%";

        return Chapter::query()
            ->where('visible', '=', true)
            ->where(static function (Builder $query) use ($like): void {
                $query->orWhere('name', 'like', $like)
                    ->orWhere('slug', 'like', $like);
            });
    }
}
