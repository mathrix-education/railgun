<?php

namespace Tests\API;

use Faker\Factory as FakeFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Constraint\RegularExpression;

/**
 * Class FilesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class FilesTest extends APITestCase
{
    protected const SEED_DIRECTORIES = [
        "a",
        "a/a1",
        "a/a1/a2",
        "b",
        "c"
    ];
    protected const SEED_FILES = [
        "root.txt",
        "a/a.txt",
        "a/a1/a1.txt",
        "a/a1/a1-prime.txt",
        "a/a1/a2/a2.txt",
        "b/b.txt",
        "c/c.txt"
    ];
    /** @var array $fileSignature The file JSON schema signature */
    protected $fileSignature;
    /** @var array $directorySignature The directory JSON schema signature */
    protected $directorySignature;


    public function setUp()
    {
        parent::setUp();

        Storage::fake("cdn");
        $disk = Storage::disk("cdn");

        // Seed CDN
        foreach (self::SEED_DIRECTORIES as $directory) {
            $disk->makeDirectory($directory);
        }
        foreach (self::SEED_FILES as $file) {
            $disk->put($file, FakeFactory::create()->text(500));
        }

        $this->fileSignature = [
            "type" => "file",
            "path" => new RegularExpression("/^[a-zA-Z0-9\-\_\/\.]+$/"),
            "timestamp" => new IsType("integer"),
            "size" => new IsType("integer"),
            "url" => new IsType("string")
        ];
        $this->directorySignature = [
            "type" => "dir",
            "path" => new RegularExpression("/^[a-zA-Z0-9\-\_\/\.]+$/"),
            "timestamp" => new IsType("integer"),
            "url" => new IsType("string")
        ];
    }


    /**
     * @return Filesystem|FilesystemAdapter
     */
    public static function getDisk()
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
        return $this->faker->randomElement(self::getDisk()->allFiles("/"));
    }


    /**
     * Get a random cdn directory
     *
     * @return string
     */
    public function randomDirectory()
    {
        return $this->faker->randomElement(self::getDisk()->allDirectories("/"));
    }


    /**
     * GET /files/{path}
     */
    public function testInfoFile()
    {
        $uri = "/files/{$this->randomFile()}";
        $this->autoMockScope("get", $uri);

        $this->json("get", $uri);

        $this->assertResponseOk();
        $this->assertJsonResponseMatches($this->fileSignature);
    }


    /**
     * GET /files/{path}
     */
    public function testInfoDirectory()
    {
        $uri = "/files/{$this->randomDirectory()}";
        $this->autoMockScope("get", $uri);

        $this->json("get", $uri);

        $this->assertResponseOk();
        $this->assertJsonResponseMatches($this->directorySignature);
    }


    /**
     * GET /files/explore/{directory}
     */
    public function testExplore()
    {
        $uri = "/files/explore/{$this->randomDirectory()}";
        $this->autoMockScope("get", $uri);

        $this->json("get", $uri);

        $this->assertResponseOk();
        $this->assertJsonResponseMatches([
            "[*].type" => "file",
            "[*].path" => new RegularExpression("/^[a-zA-Z0-9\-\_\/\.]+$/"),
            "[*].timestamp" => new IsType("integer"),
            "[*].url" => new IsType("string")
        ]);
    }


    /**
     * GET /files/explore/{directory}
     */
    public function testExploreNonExistingDirectory()
    {
        $uri = "/files/explore/idontexists";
        $this->autoMockScope("get", $uri);

        $this->json("get", $uri);

        $this->assertJsonErrorResponse(404, "file_not_found");
    }


    /**
     * GET /files/explore/{directory}
     */
    public function testExploreFile()
    {
        $uri = "/files/explore/{$this->randomFile()}";
        $this->autoMockScope("get", $uri);

        $this->json("get", $uri);
        $this->assertJsonErrorResponse(400);
    }


    /**
     * POST /files
     */
    public function testUpload()
    {
        $uri = "/files";
        $this->autoMockScope("post", $uri);

        $this->call("post", $uri, [
            "file" => UploadedFile::fake()->image("avatar.jpg"),
            "destination" => "test",
            "name" => "avatar.jpg"
        ]);

        $this->assertResponseOk();
        self::getDisk()->assertExists("test/avatar.jpg");
    }


    /**
     * PATCH /files/{file}
     */
    public function testRename()
    {
        $source = $this->randomFile();
        $destination = "$source.new";

        $uri = "/files/$source";
        $this->autoMockScope("patch", $uri);

        $this->json("patch", $uri, [
            "destination" => $destination
        ]);

        $this->assertResponseOk();
        $this->assertJsonResponseMatches($this->fileSignature);

        self::getDisk()->assertMissing($source);
        self::getDisk()->assertExists($destination);
        self::getDisk()->move($destination, $source); // Rollback
    }


    /**
     * DELETE /files/{file}
     */
    public function testDeleteFile()
    {
        $file = $this->randomFile();
        self::getDisk()->copy($file, "$file.old"); // Make a backup to restore after

        $uri = "/files/$file";
        $this->autoMockScope("delete", $uri);

        $this->json("delete", "/files/$file");

        $this->assertResponseOk();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "status" => "success",
            "message" => new IsType("string")
        ]);
        self::getDisk()->assertMissing($file);

        self::getDisk()->move("$file.old", $file); // Rollback
    }
}
