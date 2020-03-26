<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Facades\Storage;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use Tests\E2E\GraphQL\TestFilesystem;

/**
 * @testdox Mutation moveFile
 */
class MoveFileIntegrationTest extends GraphQLIntegrationTestCase
{
    use TestFilesystem;

    /**
     * @param string    $source
     * @param string    $destination
     * @param bool|null $override
     */
    private function executeMoveFile(string $source, string $destination, ?bool $override = null): void
    {
        $variables = [
            'source'      => $source,
            'destination' => $destination,
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
            $variables
        );
    }

    /**
     * @return array|string[]
     */
    private function getFiles(): array
    {
        return [
            $this->getRootDirectory() . '/foo.txt',
            $this->getRootDirectory() . '/bar.txt',
        ];
    }

    /**
     * @testdox moves a file.
     */
    public function testMoveFile(): void
    {
        [$source, $destination] = $this->getFiles();
        Storage::put($source, 'hello');

        $this->actingAsNewUser('files:move');
        $this->executeMoveFile($source, $destination);

        $this->assertResponseOk();
        $this->assertNoErrors();
        Storage::assertExists($destination);
        Storage::assertMissing($source);
    }

    /**
     * @testdox overrides an existing file.
     */
    public function testMoveFileOverride(): void
    {
        [$source, $destination] = $this->getFiles();
        Storage::put($source, 'hello');
        Storage::put($destination, 'hello');

        $this->actingAsNewUser('files:move');
        $this->executeMoveFile($source, $destination, true);

        $this->assertResponseOk();
        $this->assertNoErrors();
        Storage::assertExists($destination);
        Storage::assertMissing($source);
        Storage::delete($destination);
    }

    /**
     * @testdox do not override an existing file.
     */
    public function testMoveFileConflict(): void
    {
        [$source, $destination] = $this->getFiles();
        Storage::put($source, 'hello');
        Storage::put($destination, 'hello');

        $this->actingAsNewUser('files:move');
        $this->executeMoveFile($source, $destination, false);

        $this->assertResponseOk();
        $this->assertError();
        Storage::assertExists($destination);
        Storage::assertExists($source);
        Storage::delete($source);
        Storage::delete($destination);
    }
}
