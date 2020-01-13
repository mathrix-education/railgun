<?php

declare(strict_types=1);

namespace App\Services\GoogleCloud\Storage\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http404NotFound;
use Throwable;
use function trans;

/**
 * Thrown when a directory cannot be found on the CDN.
 */
class DirectoryNotFound extends Http404NotFound
{
    public function __construct(string $directory, ?Throwable $previous = null)
    {
        parent::__construct(
            ['directory' => $directory],
            trans('google-cloud.storage.directory_not_found', ['directory' => $directory]),
            $previous
        );
    }
}
