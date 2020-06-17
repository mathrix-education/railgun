<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Interfaces;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\Exercise as ExerciseModel;

class Exercise
{
    /**
     * The type registry.
     */
    protected TypeRegistry $typeRegistry;

    public function __construct(TypeRegistry $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * Decide which GraphQL type a resolved value has.
     *
     * @param mixed $rootValue The value that was resolved by the field. Usually an Eloquent model.
     * @param GraphQLContext $context The GraphQL context.
     * @param ResolveInfo $resolveInfo The r
     *
     * @return Type The resolved Exercise type
     *
     * @throws DefinitionException
     */
    public function __invoke(
        ExerciseModel $rootValue,
        GraphQLContext $context,
        ResolveInfo $resolveInfo
    ): Type
    {
        if ($rootValue->type === 'proof') {
            return $this->typeRegistry->get('ProofExercise');
        }

        return $this->typeRegistry->get('MCQExercise');
    }
}
