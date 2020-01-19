<?php

declare(strict_types=1);

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequest;
use Throwable;

/**
 * Thrown when a maximum depth is reached.
 */
class MaxDepth extends Http400BadRequest
{
    public function __construct(int $depth, int $maxDepth, ?Throwable $previous = null)
    {
        $message = "Expand reached maxium allowed depth of $maxDepth: got $depth";
        parent::__construct([], $message, $previous);
    }
}
