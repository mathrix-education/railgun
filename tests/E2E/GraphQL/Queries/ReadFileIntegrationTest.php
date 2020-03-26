<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use League\Flysystem\Util;
use Railgun\Facades\Storage;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use Tests\E2E\GraphQL\TestFilesystem;
use function trans;

/**
 * @testdox [E2E] Query readFile
 */
class ReadFileIntegrationTest extends GraphQLIntegrationTestCase
{
    use TestFilesystem;

    /**
     * Execute the GraphQL "readFile" query.
     *
     * @param string $path The file path.
     */
    private function executeReadFile(string $path): void
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
        $path = $this->getRootDirectory() . '/test.txt';
        Storage::put($path, 'hello');

        $this->actingAsNewUser('files:read');
        $this->executeReadFile($path);

        $this->assertResponseOk();
        $this->assertNoErrors();
    }

    /**
     * @testdox fails when trying to read a non-existing file.
     */
    public function testReadNonExistingFile(): void
    {
        $path = $this->getRootDirectory() . '/test.txt';
        Storage::delete($path);

        $this->actingAsNewUser('files:read');
        $this->executeReadFile($path);

        $this->assertResponseOk();
        $this->assertError(
            trans('storage.errors.file_not_found', ['file' => Util::normalizePath($path)]),
            'storage'
        );
    }
}
