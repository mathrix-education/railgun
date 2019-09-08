<?php

namespace App\Services\GoogleCloud\Storage\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http404NotFoundException;
use Throwable;

/**
 * Class DirectoryNotFoundException.
 * Thrown when a directory cannot be found on the CDN.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class DirectoryNotFoundException extends Http404NotFoundException
{
    public function __construct(string $directory, Throwable $previous = null)
    {
        parent::__construct(
            ["directory" => $directory],
            trans("google-cloud.storage.directory_not_found", ["directory" => $directory]),
            $previous
        );
    }
}
