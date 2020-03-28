<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use League\Flysystem\FileNotFoundException;
use Railgun\Exceptions\FileNotFoundException;
use Railgun\Facades\Storage;

class DeleteFile
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
        $disk = Storage::disk();
        $path = $args['path'];
        $file = Storage::normalize($disk->getMetadata($path));

        if ($file['type'] !== 'file') {
            throw new FileNotFoundException($path);
        }

        $disk->delete($path);

        return $file;
    }
}
