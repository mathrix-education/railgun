<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Throwable;
use function class_basename;
use function trans;

/**
 * Thrown when a user violates a policy ability.
 */
class PolicyViolationException extends HttpException
{
    /**
     * @param string|mixed   $policy   The policy object or class.
     * @param string         $ability  The policy ability.
     * @param Throwable|null $previous The previous exception, if any.
     */
    public function __construct($policy, string $ability, ?Throwable $previous = null)
    {
        parent::__construct(
            trans('auth.policy_violation', [
                'policy' => class_basename($policy) . '::' . $ability,
            ]),
            $previous,
        );
    }
}
