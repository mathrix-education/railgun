<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Scalars;

use GraphQL\Type\Definition\ScalarType;

class MixedType extends ScalarType
{
    /**
     * Handled by Eloquent model.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * Handled by Eloquent model.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function parseValue($value)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        // TODO: Implement parseLiteral() method.
    }
}
