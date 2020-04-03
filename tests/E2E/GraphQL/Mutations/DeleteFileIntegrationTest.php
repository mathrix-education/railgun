<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Facades\Storage;
use Tests\E2E\GraphQL\FilesystemTestCase;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function trans;

/**
 * @testdox deleteFile
 */
class DeleteFileIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;
    use FilesystemTestCase;

    public function executeDeleteBlob(string $path): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($path: String!) {
                deleteFile(path: $path) {
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
     * @testdox deletes an existing file.
     */
    public function testDeleteExistingFile(): void
    {
        $this->seedTestDirectory(['foo.txt']);
        $this->actingAsNewUser('files:delete');

        $this->executeDeleteBlob('foo.txt');

        $this->assertResponseOk();
        $this->assertNoErrors();
    }

    /**
     * @testdox fails to delete an non-existing files.
     */
    public function testDeleteNonExistingFile(): void
    {
        $path = $this->getTestPath('foo.txt');
        Storage::delete($path);
        $this->actingAsNewUser('files:delete');

        $this->executeDeleteBlob($path);

        $this->assertResponseOk();
        $this->assertError(trans('storage.errors.file_not_found', ['file' => $path]));
    }
}
