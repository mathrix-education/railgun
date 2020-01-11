<?php

declare(strict_types=1);

namespace Tests\Integration\GoogleCloud\Storage;

use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\OpenAPIValidation\PSR7\OperationAddress;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APIStatelessTestCase;

class FilesTest extends APIStatelessTestCase
{
    /**
     * @return array Generate the files to upload and get.
     */
    public function uploadsDataProvider(): array
    {
        return [
            'shop.png' => ['shop.png', 'shop2.png'],
            'fruits/apple.png' => ['fruits/apple.png', 'apple.png'],
            'fruits/favorites/pear.jpeg' => ['fruits/favorites/pear.jpeg', 'fruits/old/pear.jpg'],
        ];
    }

    /**
     * @return array Generate the list files and HTTP statuses.
     */
    public function listDataProvider(): array
    {
        return [
            '/' => ['/', 200],
            'fruits' => ['fruits', 200],
            'fruits/favorites' => ['fruits/favorites', 200],
            'idontexist' => ['idontexist', 404],
        ];
    }

    /**
     * @return array Generate the files to delete.
     */
    public function deleteDataProvider(): array
    {
        return [
            'shop2.png' => ['shop2.png'],
            'apple.png' => ['apple.png'],
            'fruits/old/pear.jpg' => ['fruits/old/pear.jpg'],
        ];
    }

    /**
     * POST /files/{files}
     *
     * @param string $file
     *
     * @dataProvider uploadsDataProvider
     */
    public function testUpload(string $file): void
    {
        JWT::withScopes('files:write');

        $this->call('POST', "/files/$file", [], [], [
            'file' => UploadedFile::fake()->image($this->faker->userName . '.png', 100, 100),
        ], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->getDisk()->assertExists($file);
        $this->assertResponseStatus(200);
        $this->assertOpenAPI($this->response, new OperationAddress('/files/{file}', 'post'));
    }

    /**
     * Get a disk.
     *
     * @param string|null $diskName The disk id to get.
     *
     * @return FilesystemAdapter
     */
    private function getDisk(?string $diskName = null): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);

        return $disk;
    }

    /**
     * GET /files/{file}
     *
     * @param string $file The file path to get.
     *
     * @depends      testUpload
     * @dataProvider uploadsDataProvider
     */
    public function testGet(string $file): void
    {
        JWT::withScopes('files:read');

        $this->json('get', "/files/{$file}");

        $this->assertResponseStatus(200);
        $this->assertOpenAPI($this->response, new OperationAddress('/files/{file}', 'get'));
    }

    /**
     * GET /files/list/{directory}
     *
     * @param string $directory      The directory to list.
     * @param int    $expectedStatus The expected HTTP status.
     *
     * @throws Exception
     *
     * @dataProvider listDataProvider
     */
    public function testList(string $directory, int $expectedStatus): void
    {
        JWT::withScopes('files:read');

        $this->json('get', "/files/list/$directory");

        $this->assertResponseStatus($expectedStatus);
        $this->assertOpenAPI($this->response, new OperationAddress('/files/list/{directory}', 'get'));
    }

    /**
     * PATCH /files/{file}
     *
     * @param string $file        The file to move.
     * @param string $destination The file destination.
     *
     * @depends      testUpload
     * @dataProvider uploadsDataProvider
     */
    public function testPatch(string $file, string $destination): void
    {
        JWT::withScopes('files:write');

        $this->json('patch', "/files/$file", ['destination' => $destination]);

        $this->getDisk()->assertExists($destination);
        $this->assertResponseStatus(200);
        $this->assertOpenAPI($this->response, new OperationAddress('/files/{file}', 'patch'));
    }

    /**
     * DELETE /files/{file}
     *
     * @param string $file The file to delete.
     *
     * @depends      testPatch
     * @dataProvider deleteDataProvider
     */
    public function testDelete(string $file): void
    {
        JWT::withScopes('files:delete');

        $this->json('delete', "/files/$file");

        $this->getDisk()->assertMissing($file);
        $this->assertResponseStatus(200);
        $this->assertOpenAPI($this->response, new OperationAddress('/files/{file}', 'delete'));
    }
}
