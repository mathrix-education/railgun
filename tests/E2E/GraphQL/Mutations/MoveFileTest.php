<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Facades\Storage;
use Tests\E2E\GraphQL\FilesystemTestCase;
use Tests\E2E\GraphQL\GraphQLTestCase;

class MoveFileTest extends GraphQLTestCase
{
    use DatabaseTransactions;
    use FilesystemTestCase;

    public function testMoveFile(): void
    {
        [$source, $destination] = $this->getFiles();
        Storage::put($source, 'hello');

        $this->withPermissions('files:move');
        $this->executeMoveFile($source, $destination);

        $this->assertResponseOk();
        $this->assertNoErrors();
        Storage::assertMissing($source);
        Storage::assertExists($destination);
    }

    /**
     * @return array|string[]
     */
    private function getFiles(): array
    {
        return [
            $this->getTestPath('foo.txt'),
            $this->getTestPath('bar.txt'),
        ];
    }

    /**
     * @param string    $source      The source path.
     * @param string    $destination The destination path.
     * @param bool|null $override    If the destination path should be overridden.
     */
    private function executeMoveFile(
        string $source,
        string $destination,
        ?bool $override = null
    ): void
    {
        $variables = [
            'source'      => $this->getTestPath($source),
            'destination' => $this->getTestPath($destination),
        ];

        if ($override !== null) {
            $variables['override'] = $override;
        }

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($source: String!, $destination: String!, $override: Boolean) {
                moveFile(
                    source: $source
                    destination: $destination
                    override: $override
                ) {
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
            $variables,
        );
    }

    public function testMoveFileOverride(): void
    {
        [$source, $destination] = $this->getFiles();
        Storage::put($source, 'hello');
        Storage::put($destination, 'hello');

        $this->withPermissions('files:move');
        $this->executeMoveFile($source, $destination, true);

        $this->assertResponseOk();
        $this->assertNoErrors();
        Storage::assertMissing($source);
        Storage::assertExists($destination);
    }

    public function testMoveFileConflict(): void
    {
        [$source, $destination] = $this->getFiles();
        Storage::put($source, 'hello');
        Storage::put($destination, 'hello');

        $this->withPermissions('files:move');
        $this->executeMoveFile($source, $destination, false);

        $this->assertResponseOk();
        $this->assertErrorCode('FILE_CONFLICT');
        $this->assertErrorCategory('storage');
        Storage::assertExists($source);
        Storage::assertExists($destination);
    }

    public function testMoveDirectory(): void
    {
        $this->seedTestDirectory(['foo/bar.txt']);
        $source      = $this->getTestPath('foo');
        $destination = $this->getTestPath('baz');

        $this->withPermissions('files:move');
        $this->executeMoveFile($source, $destination, false);

        $this->assertResponseOk();
        $this->assertErrorCode('FILE_NOT_FOUND');
        $this->assertErrorCategory('storage');
        Storage::assertExists($source);
        Storage::assertMissing($destination);
    }

    public function testMoveFileToDirectory(): void
    {
        $this->seedTestDirectory(['foo/bar.txt', 'baz/foo.txt']);
        $source      = $this->getTestPath('foo/bar.txt');
        $destination = $this->getTestPath('baz');

        $this->withPermissions('files:move');
        $this->executeMoveFile($source, $destination, false);

        $this->assertResponseOk();
        $this->assertNoErrors();
        Storage::assertMissing($source);
        Storage::assertExists($destination . '/bar.txt');
    }
}
