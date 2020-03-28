<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use League\Flysystem\FileNotFoundException as LeagueFileNotFoundException;
use League\Flysystem\Util;
use Railgun\Exceptions\FileNotFoundException;
use Railgun\Facades\Storage;

/**
 * Query readFile.
 * Read a file metadata at a specified path.
 */
class ReadFile
{
    /**
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The query arguments (path).
     *
     * @return mixed[] The file metadata.
     *
     * @throws LeagueFileNotFoundException
     */
    public function __invoke($rootValue, array $args): array
    {
        $path = '/' . Util::normalizePath($args['path']);
        $disk = Storage::disk();
        $file = Storage::normalize($disk->getMetadata($path));

        if ($file['type'] !== 'file') {
            throw new FileNotFoundException($path);
        }

        return $file;
    }
}
