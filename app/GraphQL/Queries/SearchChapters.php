<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Railgun\Models\Chapter;

/**
 * Query searchChapters.
 * Search chapters using a query string.
 */
class SearchChapters
{
    /**
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The query arguments (query).
     *
     * @return Builder<Chapter>
     */
    public function __invoke($rootValue, array $args): Builder
    {
        $query = $args['query'];
        $like  = "%{$query}%";

        return Chapter::query()
            ->where('name', 'LIKE', $like)
            ->orWhere('slug', 'LIKE', $like);
    }
}
