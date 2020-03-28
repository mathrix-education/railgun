<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use League\Flysystem\FileNotFoundException;
use Railgun\Exceptions\FileNotFoundException;
use Railgun\Facades\Storage;

class MoveFile
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
        $disk        = Storage::disk();
        $source      = $args['source'];
        $destination = $args['destination'];
        $file        = Storage::normalize($disk->getMetadata($source));

        if ($file['type'] !== 'file') {
            throw new FileNotFoundException($destination);
        }

        if ($args['override'] && $disk->exists($destination)) {
            $destinationFile = Storage::normalize($disk->getMetadata($destination));

            if ($destinationFile['type'] === 'dir') {
                $destination .= '/' . $file['basename'];
            } else {
                $disk->delete($destination);
            }
        }

        $disk->move($source, $destination);

        return Storage::normalize($disk->getMetadata($destination));
    }
}
