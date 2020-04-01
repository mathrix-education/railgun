<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Railgun\Models\Course;

/**
 * Query searchCourses.
 * Search courses using a query string.
 */
class SearchCourses
{
    /**
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The query arguments (query).
     *
     * @return Builder<Course>
     */
    public function __invoke($rootValue, array $args): Builder
    {
        $query = $args['query'];
        $like  = "%{$query}%";

        return Course::query()
            ->where('name', 'LIKE', $like)
            ->orWhere('slug', 'LIKE', $like)
            ->orWhere('seo_title', 'LIKE', $like)
            ->orWhere('seo_slug', 'LIKE', $like)
            ->orWhere('seo_keywords', 'LIKE', $like)
            ->orWhere('seo_description', 'LIKE', $like)
            ->orWhere('content', 'LIKE', $like);
    }
}
