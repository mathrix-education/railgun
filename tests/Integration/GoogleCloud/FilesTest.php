<?php

namespace Tests\Integration\GoogleCloud;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APITestCase;

/**
 * Class FilesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class FilesTest extends APITestCase
{
    /**
     * @return Filesystem|FilesystemAdapter
     */
    public function getDisk()
    {
        return Storage::disk("cdn");
    }


    /**
     * Get a random cdn file
     *
     * @return mixed
     */
    public function randomFile()
    {
        return $this->faker->randomElement($this->getDisk()->allFiles("/"));
    }


    /**
     * Get a random cdn directory
     *
     * @return string
     */
    public function randomDirectory()
    {
        return $this->faker->randomElement($this->getDisk()->allDirectories("/"));
    }


    /**
     * GET /files/{path}
     */
    public function testInfoFile()
    {
        $uri = "/files/{$this->randomFile()}";
        JWT::autoScope("get", $uri);

        $this->json("get", $uri);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * GET /files/explore/{directory}
     */
    public function testExplore()
    {
        $uri = "/files/explore/{$this->randomDirectory()}";
        JWT::autoScope("get", $uri);

        $this->json("get", $uri);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * GET /files/explore
     * Special test for root directory (edgy case)
     */
    public function testExploreRoot()
    {
        $uri = "/files/explore";
        JWT::autoScope("get", $uri);

        $this->json("get", $uri);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * GET /files/explore/{directory}
     */
    public function testExploreNonExistingDirectory()
    {
        $uri = "/files/explore/idontexists";
        JWT::autoScope("get", $uri);

        $this->json("get", $uri);
        $this->assertOpenAPIResponse();
    }


    /**
     * GET /files/explore/{directory}
     */
    public function testExploreFile()
    {
        $uri = "/files/explore/{$this->randomFile()}";
        JWT::autoScope("get", $uri);

        $this->json("get", $uri);
        $this->assertOpenAPIResponse();
    }


    /**
     * POST /files
     */
    public function testUpload()
    {
        $uri = "/files/test/avatar.jpg";
        JWT::autoScope("post", $uri);

        $this->requestMethod = "post";
        $this->requestUri = $uri;
        $this->call($this->requestMethod, $this->requestUri, [], [], [
            "file" => UploadedFile::fake()->image("avatar.jpg")
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
        $this->getDisk()->assertExists("test/avatar.jpg");
        $this->getDisk()->delete("test/avatar.jpg"); // Rollback
    }


    /**
     * PATCH /files/{file}
     */
    public function testRename()
    {
        $source = $this->randomFile();
        $destination = "$source.new";

        $uri = "/files/$source";
        JWT::autoScope("patch", $uri);

        $this->json("patch", $uri, [
            "destination" => $destination
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

        $this->getDisk()->assertMissing($source);
        $this->getDisk()->assertExists($destination);
        $this->getDisk()->move($destination, $source); // Rollback
    }


    /**
     * DELETE /files/{file}
     */
    public function testDeleteFile()
    {
        $file = $this->randomFile();
        $this->getDisk()->copy($file, "$file.old"); // Make a backup to restore after

        $uri = "/files/$file";
        JWT::autoScope("delete", $uri);

        $this->json("delete", "/files/$file");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

        $this->getDisk()->assertMissing($file);
        $this->getDisk()->move("$file.old", $file); // Rollback
    }
}
