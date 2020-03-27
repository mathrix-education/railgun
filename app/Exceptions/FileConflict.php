<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use League\Flysystem\Util;
use Throwable;
use function trans;

/**
 * Thrown when the users tries to override a file.
 */
class FileConflict extends HttpException
{
    protected string $category = 'storage';

    public function __construct(string $file, ?Throwable $previous = null)
    {
        parent::__construct(
            trans('storage.errors.file_conflict', ['file' => Util::normalizePath($file)]),
            $previous
        );
    }
}
