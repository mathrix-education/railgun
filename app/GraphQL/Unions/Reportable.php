<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Unions;

use GraphQL\Type\Definition\Type;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Railgun\Models\Exercise;
use function class_basename;

class Reportable
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
     *
     * @return Type The Notable resolved type.
     *
     * @throws DefinitionException
     */
    public function __invoke($rootValue): Type
    {
        if ($rootValue instanceof Exercise) {
            if ($rootValue->type === 'proof') {
                return $this->typeRegistry->get('ProofExercise');
            }

            return $this->typeRegistry->get('MCQExercise');
        }

        return $this->typeRegistry->get(class_basename($rootValue));
    }
}
