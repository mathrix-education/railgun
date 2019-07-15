<?php

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;

/**
 * Class ProcessorException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.7
 */
class ProcessorDeclinedException extends Http400BadRequestException
{
}
