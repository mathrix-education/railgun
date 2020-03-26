<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use League\Flysystem\Util;
use Railgun\Facades\Storage;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function trans;

/**
 * @testdox [E2E] Query readFile
 */
class ReadFileIntegrationTest extends GraphQLIntegrationTestCase
{
    private function execute(string $path): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query($path: String!) {
                readFile(path: $path) {
                    path
                    basename
                    url
                    basename
                    dirname
                    mimetype
                    size
                    updated_at
                }
            }
            GRAPHQL,
            ['path' => $path]
        );
    }

    /**
     * @testdox returns the file metadata of and existing file.
     */
    public function testReadExistingFile(): void
    {
        $path = '/test.txt';
        Storage::put($path, 'hello');

        $this->actingAsNewUser('files:read');
        $this->execute($path);

        $this->assertResponseOk();
        $this->assertNoErrors();
    }

    /**
     * @testdox fails when trying to read a non-existing file.
     */
    public function testReadNonExistingFile(): void
    {
        $path = '/idontexist.txt';

        if (Storage::exists($path)) {
            Storage::delete($path);
        }

        $this->actingAsNewUser('files:read');
        $this->execute($path);

        $this->assertResponseOk();
        $this->assertError(
            trans('storage.errors.file_not_found', ['file' => Util::normalizePath($path)]),
            'storage'
        );
    }
}
