<?php

namespace App\Services\GoogleCloud\Storage\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http404NotFoundException;
use Throwable;

/**
 * Class FileNotFoundException.
 * Thrown when a file cannot be found on the CDN.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class FileNotFoundException extends Http404NotFoundException
{
    public function __construct(string $file, Throwable $previous = null)
    {
        parent::__construct(
            ["file" => $file],
            trans("google-cloud.storage.file_not_found", ["file" => $file]),
            $previous
        );
    }
}
