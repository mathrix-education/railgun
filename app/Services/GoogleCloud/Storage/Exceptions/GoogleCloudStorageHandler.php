<?php

declare(strict_types=1);

namespace App\Services\GoogleCloud\Storage\Exceptions;

use League\Flysystem\FileNotFoundException as FlysystemFileNotFoundException;
use Throwable;

/**
 * Handle CDN exceptions.
 */
class GoogleCloudStorageHandler
{
    /**
     * @throws FileNotFoundException
     */
    public static function handle(Throwable $exception): void
    {
        if ($exception instanceof FlysystemFileNotFoundException) {
            throw new FileNotFoundException($exception->getPath());
        }
    }
}
