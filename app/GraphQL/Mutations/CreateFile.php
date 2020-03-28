<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Http\UploadedFile;
use League\Flysystem\FileNotFoundException;
use Railgun\Facades\Storage;
use function basename;
use function dirname;

class CreateFile
{
    /**
     * Return a value for the field.
     *
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The mutation arguments.
     *
     * @return mixed[] The create file metadata.
     *
     * @throws FileNotFoundException
     */
    public function __invoke($rootValue, array $args): array
    {
        /** @var UploadedFile $file */
        $file = $args['file'];
        $path = $args['path'];

        $file->storePubliclyAs(dirname($path), basename($path));

        return Storage::normalize(Storage::disk()->getMetadata($path));
    }
}
