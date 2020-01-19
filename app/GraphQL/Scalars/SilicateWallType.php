<?php

declare(strict_types=1);

namespace App\GraphQL\Scalars;

use GraphQL\Type\Definition\ScalarType;

class SilicateWallType extends ScalarType
{
    /**
     * @inheritDoc
     */
    public function serialize($value)
    {
        // TODO: Implement serialize() method.
    }

    /**
     * @inheritDoc
     */
    public function parseValue($value)
    {
        // TODO: Implement parseValue() method.
    }

    /**
     * @inheritDoc
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        // TODO: Implement parseLiteral() method.
    }
}
