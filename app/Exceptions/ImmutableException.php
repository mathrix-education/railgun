<?php

namespace App\Exceptions;

use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class ImmutableException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc
 */
class ImmutableException extends Http400BadRequestException
{

}
