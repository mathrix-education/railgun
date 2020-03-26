<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use League\Flysystem\FileNotFoundException;
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
        $disk = Storage::disk();

        if ($args['override'] && $disk->exists($args['destination'])) {
            $disk->delete($args['destination']);
        }

        $disk->move($args['source'], $args['destination']);

        return Storage::normalize($disk->getMetadata($args['destination']));
    }
}
