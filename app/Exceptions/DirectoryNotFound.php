<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use League\Flysystem\Util;
use Throwable;
use function trans;

/**
 * Thrown when a user tries to access a non-existing directory.
 */
class DirectoryNotFound extends HttpException
{
    protected string $category = 'storage';

    public function __construct(string $directory, ?Throwable $previous = null)
    {
        parent::__construct(
            trans('storage.errors.directory_not_found', ['directory' => Util::normalizePath($directory)]),
            $previous
        );
    }
}
