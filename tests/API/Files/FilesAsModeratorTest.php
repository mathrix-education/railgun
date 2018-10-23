<?php

namespace Tests\API\Files;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Constraint\IsType;

/**
 * Class FilesAsModeratorTest
 * Test files as moderator.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   4.1.1
 */
class FilesAsModeratorTest extends FilesAsRedactorTest
{
    protected const SEED_DIRECTORIES = ["a", "a/a1", "a/a1/a2", "b", "c"];
    protected const SEED_FILES = ["root.txt", "a/a.txt", "a/a1/a1.txt", "a/a1/a1-prime.txt", "a/a1/a2/a2.txt",
        "c/c.txt"];

    public static function setUpBeforeClass()
    {
        $disk = Storage::disk("cdn");

        // Clean directories
        foreach ($disk->directories() as $directory) {
            $disk->deleteDirectory($directory);
        }

        // Clean files except .gitignore
        foreach ($disk->files() as $file) {
            if ($file !== ".gitignore") {
                $disk->delete($file);
            }
        }

        // Seed CDN
        foreach (self::SEED_DIRECTORIES as $directory) {
            $disk->makeDirectory($directory);
        }

        foreach (self::SEED_FILES as $file) {
            $disk->put($file, \Faker\Factory::create()->text(500));
        }
    }

    public function randomDirectory()
    {
        return $this->faker->randomElement(self::SEED_DIRECTORIES);
    }

    public function randomFile()
    {
        return $this->faker->randomElement(self::SEED_FILES);
    }

    /**
     * GET /files/{directory}
     *
     * directory: existing directory on the CDN
     * Assert HTTP 200 Success
     */
    public function testExplore()
    {
        $this->json("get", "/files/{$this->randomDirectory()}");

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatchesSchema($this->response->getContent(), [
            "*" => new IsType("string")
        ]);
    }

    /**
     * GET /files/{directory}
     *
     * directory: not existing directory on the CDN
     * Assert HTTP 404 file_not_found
     */
    public function testExploreNonExistingDirectory()
    {
        $this->json("get", "/files/idontexists");

        $this->assertHttpError(404, "file_not_found");
    }

    /**
     * GET /files/{directory}
     *
     * directory: existing file
     * Assert HTT 400 invalid_exception
     */
    public function testExploreFile()
    {
        $this->json("get", "/files/{$this->randomFile()}");

        $this->assertHttp400InvalidRequest();
    }
}
