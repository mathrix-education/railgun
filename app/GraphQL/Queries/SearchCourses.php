<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Railgun\Models\Course;
use function func_get_arg;

class SearchCourses
{
    /**
     * @return Builder The search builder.
     */
    public function getBuilder(): Builder
    {
        $args  = func_get_arg(1);
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
