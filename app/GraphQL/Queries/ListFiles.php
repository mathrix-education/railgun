<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Railgun\Exceptions\DirectoryNotFound;
use Railgun\Facades\Storage;
use function collect;
use function trim;

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
        $directory = '/' . trim($args['path'], " \t\n\r \v/");
        $disk      = Storage::disk();

        if ($directory !== '/' && !$disk->exists($directory)) {
            throw new DirectoryNotFound($directory);
        }

        $content     = collect($disk->listContents($directory))->map(fn($o) => Storage::normalize($o));
        $directories = $content->filter(fn($o) => $o['type'] === 'dir')->values();
        $files       = $content->filter(fn($o) => $o['type'] === 'file')->values();

        return [
            'directories' => $directories,
            'files'       => $files,
        ];
    }
}
