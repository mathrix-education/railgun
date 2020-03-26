<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use League\Flysystem\Util;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function trans;

/**
 * @testdox [E2E] Mutation listFiles
 */
class ListFilesIntegrationTest extends GraphQLIntegrationTestCase
{
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
     * @testdox returns the content of and existing directory.
     */
    public function testReadExistingDirectory(): void
    {
        $this->actingAsNewUser('files:read');
        $this->executeListFiles('/');

        $this->assertNoErrors();
        $this->assertResponseOk();
    }

    /**
     * @testdox fails when trying to list a non-existing directory.
     */
    public function testReadNonExistingDirectory(): void
    {
        $directory = Util::normalizePath('/idontexist');
        $this->actingAsNewUser('files:read');
        $this->executeListFiles($directory);

        $this->assertResponseOk();
        $this->assertError(
            trans('storage.errors.directory_not_found', ['directory' => $directory]),
            'storage'
        );
    }
}
