<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use League\Flysystem\FileNotFoundException as LeagueFileNotFoundException;
use Railgun\Exceptions\FileNotFoundException;
use Railgun\Facades\Storage;

/**
 * Mutation deleteFile.
 * Delete a file on the CDN.
 */
class DeleteFile
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The mutation arguments.
     *
     * @return mixed[] The delete file metadata.
     *
     * @throws FileNotFoundException
     * @throws LeagueFileNotFoundException
     */
    public function __invoke($_, array $args): array
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
