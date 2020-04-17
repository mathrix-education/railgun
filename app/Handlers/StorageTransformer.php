<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Closure;
use League\Flysystem\FileExistsException as LeagueFileExistsException;
use League\Flysystem\FileNotFoundException as LeagueFileNotFoundException;
use Railgun\Exceptions\FileConflictException;
use Railgun\Exceptions\FileNotFoundException;
use Throwable;

class StorageTransformer implements TransformsExceptions
{
    /**
     * Transform a exception into another.
     *
     * @param Throwable $original The original exception.
     * @param Closure   $next     The next exception transformer.
     *
     * @return Throwable The transformed exception.
     */
    public function transform(Throwable $original, Closure $next): Throwable
    {
        if ($original instanceof LeagueFileNotFoundException) {
            // Convert League exception
            return $next(new FileNotFoundException($original->getPath(), $original));
        }

        if ($original instanceof LeagueFileExistsException) {
            // Convert League exception
            return $next(new FileConflictException($original->getPath(), $original));
        }

        return $next($original);
    }
}
