<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Facades\Storage;
use Tests\E2E\GraphQL\FilesystemTestCase;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function collect;
use function trans;

/**
 * @testdox [E2E] Mutation listFiles
 */
class ListFilesIntegrationTest extends GraphQLIntegrationTestCase
{
    use FilesystemTestCase;

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
            ['path' => $path]
        );
    }

    /**
     * @testWith ["/"]
     *           [""]
     * @testdox returns the content of the root directory.
     *
     * @param string $path
     */
    public function testReadRootDirectory(string $path): void
    {
        $this->actingAsNewUser('files:read');
        $this->executeListFiles($path);

        $this->assertNoErrors();
        $this->assertResponseOk();
    }

    /**
     * @testdox returns the content of an existing directory.
     */
    public function testReadExistingDirectory(): void
    {
        $files = ['foo.txt', 'bar.txt', 'baz/foo.txt'];

        collect($files)->each(function (string $file) {
            Storage::put($this->getTestDirectory() . '/' . $file, 'hello');
        });

        $this->actingAsNewUser('files:read');
        $this->executeListFiles($this->getTestDirectory());

        $this->assertNoErrors();
        $this->assertResponseOk();
    }

    /**
     * @testdox fails when trying to list a non-existing directory.
     */
    public function testReadNonExistingDirectory(): void
    {
        $directory = $this->getTestPath('idontexists');
        $this->actingAsNewUser('files:read');
        $this->executeListFiles($directory);

        $this->assertResponseOk();
        $this->assertError(
            trans('storage.errors.directory_not_found', ['directory' => $directory]),
            'storage'
        );
    }
}
