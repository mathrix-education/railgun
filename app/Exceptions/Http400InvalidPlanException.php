<?php
namespace App\Exceptions;

use Throwable;

/**
 * Class Http400InvalidPlanException
 *
 * @author Mathrix Back-End Team
 * @package App\Exceptions
 * @since 4.1.0
 * @copyright Mathrix SA
 */
class Http400InvalidPlanException extends Http400InvalidRequestException
{
    public function __construct(string $plan, int $code = 0, ?Throwable $previous = null, array $data = [])
    {
        parent::__construct("Invalid plan `$plan`", $code, $previous, $data);
    }
}
