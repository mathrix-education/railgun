<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\Chapter;

class ReadChapterBySlugs
{
    /**
     * @param null           $rootValue
     * @param mixed[]        $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return Chapter::query()
            ->whereHas('grade', static function (Builder $builder) use ($args) {
                $builder->where('slug', '=', $args['gradeSlug']);
            })
            ->whereHas('discipline', static function (Builder $builder) use ($args) {
                $builder->where('slug', '=', $args['disciplineSlug']);
            })
            ->where('slug', '=', $args['chapterSlug'])
            ->first();
    }
}
