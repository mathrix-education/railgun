<?php
namespace App\Exceptions;

use Throwable;

/**
 * Class Http400InvalidPlanException
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Exceptions
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class Http400InvalidPlanException extends Http400InvalidRequestException
{
    public function __construct(string $plan, int $code = 0, ?Throwable $previous = null, array $data = [])
    {
        parent::__construct("Invalid plan `$plan`", $code, $previous, $data);
    }
}
