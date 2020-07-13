<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\Exercise;

class UpdateExercise
{
    /**
     * @param null           $_           The parent value, always null in this case.
     * @param mixed[]        $args        The mutation arguments.
     * @param GraphQLContext $context     Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo    $resolveInfo Information about the query itself, such as the execution state, the field
     *                                    name, path to the field from the root, and more.
     *
     * @return Exercise The updated Exercise.
     */
    public function __invoke(
        $_,
        array $args,
        GraphQLContext $context,
        ResolveInfo $resolveInfo
    ): Exercise
    {
        if ($resolveInfo->fieldName === 'updateProofExercise') {
            $type = 'proof';
        } else {
            $type = 'mcq';
        }

        /** @var Exercise $exercise */
        $exercise = Exercise::query()
            ->where('type', '=', $type)
            ->where('id', '=', $args['id'])
            ->firstOrFail();

        $exercise->update($args);

        return $exercise;
    }
}
