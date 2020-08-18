<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Laravel\Lumen\Testing\DatabaseTransactions;
use League\Flysystem\Util;
use Railgun\Facades\Storage;
use Tests\E2E\GraphQL\FilesystemTestCase;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function trans;

class ReadFileTest extends GraphQLTestCase
{
    use DatabaseTransactions;
    use FilesystemTestCase;

    public function testReadExistingFile(): void
    {
        $file = 'test.txt';
        $this->seedTestDirectory($file);

        $this->withPermissions('files:read');
        $this->executeReadFile($file);

        $this->assertResponseOk();
        $this->assertNoErrors();
    }

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

    public function testReadNonExistingFile(): void
    {
        $file = '/test.txt';
        $path = $this->getTestPath($file);
        Storage::delete($path);

        $this->withPermissions('files:read');
        $this->executeReadFile($file);

        $this->assertResponseOk();
        $this->assertErrorCode('FILE_NOT_FOUND');
        $this->assertErrorCategory('storage');
        $this->assertErrorMessage(trans('storage.errors.file_not_found', ['file' => Util::normalizePath($path)]));
    }

    public function testReadDirectory(): void
    {
        $dir  = 'foo';
        $path = $this->getTestPath($dir);
        $this->seedTestDirectory(['foo/test.txt']);

        $this->withPermissions('files:read');
        $this->executeReadFile($dir);

        $this->assertResponseOk();
        $this->assertErrorCode('FILE_NOT_FOUND');
        $this->assertErrorCategory('storage');
        $this->assertErrorMessage(trans('storage.errors.file_not_found', ['file' => Util::normalizePath($path)]));
    }
}
