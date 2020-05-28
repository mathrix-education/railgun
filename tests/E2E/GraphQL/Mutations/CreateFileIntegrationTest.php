<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Http\UploadedFile;
use JsonException;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Facades\Storage;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * @testdox Mutation createFile
 */
class CreateFileIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * Execute the GraphQL "createFile" mutation.
     *
     * @param string $path The file path.
     *
     * @throws JsonException
     */
    private function executeCreateFile(string $path): void
    {
        $data = [
            'operations' => [
                'query'     => <<<'GRAPH'
                mutation($file: Upload!, $path: String!) {
                    createFile(file: $file, path: $path) {
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
                GRAPH,
                'variables' => [
                    'file' => null,
                    'path' => $path,
                ],
            ],
            'map'        => ['0' => ['variables.file']],
        ];

        $data['operations'] = json_encode($data['operations'], JSON_THROW_ON_ERROR, 512);
        $data['map']        = json_encode($data['map'], JSON_THROW_ON_ERROR, 512);
        $variables          = [
            '0' => UploadedFile::fake()->create('image.jpg', 500),
        ];

        $this->multipartGraphQL($data, $variables);
    }

    /**
     * @testdox creates a new file
     *
     * @throws JsonException
     */
    public function testCreateFile(): void
    {
        $path = 'foo/bar/test.jpg';
        Storage::delete($path);
        $this->withPermissions('files:write');

        $this->executeCreateFile($path);

        $this->assertResponseOk();
        $this->assertNoErrors();
        Storage::assertExists($path);
        Storage::delete($path);
    }

    /**
     * @testdox overrides an existing file
     *
     * @throws JsonException
     */
    public function testOverrideFile(): void
    {
        $path = 'foo/bar/override.jpg';
        Storage::put($path, 'hello world');
        $this->withPermissions('files:write');

        $this->executeCreateFile($path);

        $this->assertResponseOk();
        $this->assertNoErrors();
        Storage::assertExists($path);
        Storage::delete($path);
    }
}
