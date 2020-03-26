<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use League\Flysystem\Util;
use Railgun\Exceptions\FileNotFound;
use Railgun\Facades\Storage;
use function collect;
use function dirname;

class ReadFile
{
    /**
     * @param null    $rootValue
     * @param mixed[] $args
     *
     * @return mixed
     */
    public function __invoke($rootValue, array $args)
    {
        $path = '/' . Util::normalizePath($args['path']);
        $disk = Storage::disk();

        if (!$disk->exists($path)) {
            throw new FileNotFound($path);
        }

        $file = collect($disk->listContents(dirname($path)))->first(static function ($object) use ($path) {
            return $object['type'] === 'file'
                && Util::normalizePath($object['path']) === Util::normalizePath($path);
        });

        if ($file === null) {
            throw new FileNotFound($path);
        }

        return Storage::normalize($file);
    }
}
