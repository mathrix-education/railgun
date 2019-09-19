<?php

declare(strict_types=1);

namespace App\Services\GoogleCloud\Storage\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http404NotFoundException;
use Throwable;
use function trans;

/**
 * Thrown when a file cannot be found on the CDN.
 */
class FileNotFound extends Http404NotFoundException
{
    public function __construct(string $file, ?Throwable $previous = null)
    {
        parent::__construct(
            ['file' => $file],
            trans('google-cloud.storage.file_not_found', ['file' => $file]),
            $previous
        );
    }
}
