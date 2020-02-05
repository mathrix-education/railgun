<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\Chapter;

class ListChaptersByGradeSlugAndDisciplineSlug
{
    /**
     * Return a value for the field.
     *
     * @param null           $rootValue   Usually contains the result returned from the parent field. In this case, it
     *                                    is always `null`.
     * @param mixed[]        $args        The arguments that were passed into the field.
     * @param GraphQLContext $context     Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo    $resolveInfo Information about the query itself, such as the execution state, the field
     *                                    name, path to the field from the root, and more.
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
            ->get();
    }
}
