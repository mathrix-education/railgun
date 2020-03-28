<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use League\Flysystem\Util;
use Railgun\Exceptions\DirectoryNotFoundException;
use Railgun\Facades\Storage;
use function collect;

class ListFiles
{
    /**
     * @param null    $rootValue
     * @param mixed[] $args
     *
     * @return mixed
     */
    public function __invoke($rootValue, array $args)
    {
        $path = Util::normalizePath($args['path']);
        $path = $path === '' ? '/' : $path;

        $disk = Storage::disk();

        if ($path !== '/' && !$disk->exists($path)) {
            throw new DirectoryNotFoundException($path);
        }

        $content     = collect($disk->listContents($path))->map(fn($o) => Storage::normalize($o));
        $directories = $content->filter(fn($o) => $o['type'] === 'dir')->values();
        $files       = $content->filter(fn($o) => $o['type'] === 'file')->values();

        return [
            'directories' => $directories,
            'files'       => $files,
        ];
    }
}
