<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Util;
use Railgun\Exceptions\FileNotFoundException;
use Railgun\Facades\Storage;

class ReadFile
{
    /**
     * @param null    $rootValue
     * @param mixed[] $args
     *
     * @return mixed
     *
     * @throws FileNotFoundException
     */
    public function __invoke($rootValue, array $args)
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
