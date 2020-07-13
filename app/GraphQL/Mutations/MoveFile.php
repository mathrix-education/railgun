<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use League\Flysystem\FileNotFoundException;
use Railgun\Facades\Storage;

/**
 * Mutation moveFile.
 * Move a file to another place on the CDN.
 */
class MoveFile
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The mutation arguments.
     *
     * @return mixed[] The moved file metadata.
     *
     * @throws FileNotFoundException
     */
    public function __invoke($_, array $args): array
    {
        $disk        = Storage::disk();
        $source      = $args['source'];
        $destination = $args['destination'];
        $file        = Storage::normalize($disk->getMetadata($source));

        if ($file['type'] !== 'file') {
            throw new FileNotFoundException($destination);
        }

        if ($disk->exists($destination)) {
            $destinationFile = Storage::normalize($disk->getMetadata($destination));

            if ($destinationFile['type'] === 'dir') {
                $destination .= '/' . $file['basename'];
            } elseif ($args['override']) {
                $disk->delete($destination);
            }
        }

        $disk->move($source, $destination);

        return Storage::normalize($disk->getMetadata($destination));
    }
}
