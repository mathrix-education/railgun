<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Interfaces;

use GraphQL\Type\Definition\Type;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use function class_basename;

class Notification
{
    /** @var TypeRegistry The type registry. */
    protected TypeRegistry $typeRegistry;

    /**
     * @param TypeRegistry $typeRegistry The injected type registry.
     */
    public function __construct(TypeRegistry $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * Decide which GraphQL type a resolved value has.
     *
     * @param mixed $rootValue The notification payload
     *
     * @return Type The Notification type.
     *
     * @throws DefinitionException
     */
    public function __invoke(\Railgun\Models\Notification $rootValue): Type
    {
        return $this->typeRegistry->get(class_basename($rootValue->type));
    }
}
