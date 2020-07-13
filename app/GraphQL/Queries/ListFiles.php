<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use League\Flysystem\Util;
use Railgun\Exceptions\DirectoryNotFoundException;
use Railgun\Facades\Storage;
use function collect;

/**
 * Query listFiles.
 * List the directories and files in a directory of the CDN.
 */
class ListFiles
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The query arguments (path).
     *
     * @return mixed
     */
    public function __invoke($_, array $args)
    {
        $path = Util::normalizePath($args['path']);
        $path = $path === '' ? '/' : $path;

        $disk = Storage::disk();

        if ($path !== '/' && !$disk->exists($path)) {
            throw new DirectoryNotFoundException($path);
        }

        $content     = collect($disk->listContents($path))->map(static fn($o) => Storage::normalize($o));
        $directories = $content->filter(static fn($o) => $o['type'] === 'dir')->values();
        $files       = $content->filter(static fn($o) => $o['type'] === 'file')->values();

        return [
            'directories' => $directories,
            'files'       => $files,
        ];
    }
}
