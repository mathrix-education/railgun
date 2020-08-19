<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Facades\Storage;
use Tests\E2E\GraphQL\FilesystemTestCase;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function collect;
use function trans;

class ListFilesTest extends GraphQLTestCase
{
    use DatabaseTransactions;
    use FilesystemTestCase;

    /**
     * @testWith ["/"]
     *           [""]
     *
     * @param string $path ,
     */
    public function testReadRootDirectory(string $path): void
    {
        $this->withPermissions('files:read');
        $this->executeListFiles($path);

        $this->assertNoErrors();
        $this->assertResponseOk();
    }

    /**
     * @param string $path The directory path
     */
    private function executeListFiles(string $path): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query($path: String!) {
                listFiles(path: $path) {
                    directories {
                        path
                        url
                        basename
                        dirname
                    }
                    files {
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
            }
            GRAPHQL,
            ['path' => $path],
        );
    }

    public function testReadExistingDirectory(): void
    {
        $files = ['foo.txt', 'bar.txt', 'baz/foo.txt'];

        collect($files)->each(function (string $file): void {
            Storage::put($this->getTestDirectory() . '/' . $file, 'hello');
        });

        $this->withPermissions('files:read');
        $this->executeListFiles($this->getTestDirectory());

        $this->assertNoErrors();
        $this->assertResponseOk();
    }

    public function testReadNonExistingDirectory(): void
    {
        $directory = $this->getTestPath('idontexists');
        $this->withPermissions('files:read');
        $this->executeListFiles($directory);

        $this->assertResponseOk();
        $this->assertErrorCode('DIRECTORY_NOT_FOUND');
        $this->assertErrorCategory('storage');
        $this->assertErrorMessage(trans('storage.errors.directory_not_found', ['directory' => $directory]));
    }
}
