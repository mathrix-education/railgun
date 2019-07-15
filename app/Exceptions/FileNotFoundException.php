<?php

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http404NotFoundException;
use Throwable;

/**
 * Class FileNotFoundException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class FileNotFoundException extends Http404NotFoundException
{
    public function __construct(string $file, Throwable $previous = null)
    {
        parent::__construct(["file" => $file], "Unable to find file `$file`", $previous);
    }
}
