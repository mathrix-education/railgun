<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Http\UploadedFile;
use League\Flysystem\FileNotFoundException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Facades\Storage;
use function basename;
use function dirname;

class CreateFile
{
    /**
     * Return a value for the field.
     *
     * @param null           $rootValue
     * @param mixed[]        $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return array
     *
     * @throws FileNotFoundException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        /** @var UploadedFile $file */
        $file = $args['file'];
        $path = $args['path'];

        $file->storePubliclyAs(dirname($path), basename($path));

        return Storage::normalize(Storage::disk()->getMetadata($path));
    }
}
