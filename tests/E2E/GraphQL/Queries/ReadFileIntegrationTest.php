<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use League\Flysystem\Util;
use Railgun\Facades\Storage;
use Tests\E2E\GraphQL\FilesystemTestCase;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function trans;

/**
 * @testdox [E2E] Query readFile
 */
class ReadFileIntegrationTest extends GraphQLIntegrationTestCase
{
    use FilesystemTestCase;

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
            ['path' => $this->getTestPath($path)],
        );
    }

    /**
     * @testdox returns the file metadata of and existing file.
     */
    public function testReadExistingFile(): void
    {
        $file = 'test.txt';
        $this->seedTestDirectory($file);

        $this->actingAsNewUser('files:read');
        $this->executeReadFile($file);

        $this->assertResponseOk();
        $this->assertNoErrors();
    }

    /**
     * @testdox fails when trying to read a non-existing file.
     */
    public function testReadNonExistingFile(): void
    {
        $file = '/test.txt';
        $path = $this->getTestPath($file);
        Storage::delete($path);

        $this->actingAsNewUser('files:read');
        $this->executeReadFile($file);

        $this->assertResponseOk();
        $this->assertError(
            trans('storage.errors.file_not_found', ['file' => Util::normalizePath($path)]),
            'storage',
        );
    }

    /**
     * @testdox fails when trying to read a directory.
     */
    public function testReadDirectory(): void
    {
        $dir  = 'foo';
        $path = $this->getTestPath($dir);
        $this->seedTestDirectory(['foo/test.txt']);

        $this->actingAsNewUser('files:read');
        $this->executeReadFile($dir);

        $this->assertResponseOk();
        $this->assertError(
            trans('storage.errors.file_not_found', ['file' => $path]),
            'storage',
        );
    }
}
