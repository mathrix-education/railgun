<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Railgun\Models\Chapter;
use function func_get_arg;

class SearchChapters
{
    /**
     * @return Builder The search builder.
     */
    public function getBuilder(): Builder
    {
        $args  = func_get_arg(1);
        $query = $args['query'];
        $like  = "%{$query}%";

        return Chapter::query()
            ->where('name', 'LIKE', $like)
            ->orWhere('slug', 'LIKE', $like);
    }
}
