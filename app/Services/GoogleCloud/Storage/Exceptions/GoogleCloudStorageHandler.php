<?php

namespace App\Services\GoogleCloud\Storage\Exceptions;

use Exception;
use League\Flysystem\FileNotFoundException as FlysystemFileNotFoundException;

/**
 * Class GoogleCloudStorageHandler.
 * Handle CDN exceptions.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class GoogleCloudStorageHandler
{
    /**
     * @param Exception $exception
     *
     * @throws FileNotFoundException
     */
    public static function handle($exception)
    {
        if ($exception instanceof FlysystemFileNotFoundException) {
            throw new FileNotFoundException($exception->getPath());
        }
    }
}
