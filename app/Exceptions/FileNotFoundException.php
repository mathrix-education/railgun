<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use League\Flysystem\Util;
use Throwable;
use function trans;

/**
 * Thrown when a user tries to access a non-existing file.
 */
class FileNotFoundException extends HttpException
{
    protected string $category = 'storage';

    public function __construct(string $file, ?Throwable $previous = null)
    {
        parent::__construct(
            trans('storage.errors.file_not_found', ['file' => Util::normalizePath($file)]),
            $previous,
        );
    }
}
