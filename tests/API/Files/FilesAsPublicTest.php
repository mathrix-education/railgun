<?php
namespace Tests\API\Files;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Constraint\RegularExpression;
use Tests\API\ScopeTestCase;

/**
 * Class FilesAsPublicTest.
 * Test files as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.1
 * @copyright Mathrix Education SA
 * @package   Tests\API\Files
 */
class FilesAsPublicTest extends ScopeTestCase
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
        "c/c.txt"
    ];

    /** @var string $cdnSubFolderUuid The test uuid folder */
    protected $cdnSubFolderUuid;
    /** @var array $fileSignature The file JSON schema signature */
    protected $fileSignature;
    /** @var array $directorySignature The directory JSON schema signature */
    protected $directorySignature;

    /**
     * @return Filesystem|FilesystemAdapter
     */
    public static function getDisk()
    {
        return Storage::disk("cdn");
    }

    public function setUp()
    {
        parent::setUp();

        $disk = Storage::disk("cdn");
        $this->cdnSubFolderUuid = $this->faker->uuid;

        // Seed CDN
        foreach (self::SEED_DIRECTORIES as $directory) {
            $disk->makeDirectory($this->cdnSubFolderUuid . "/" . $directory);
        }

        foreach (self::SEED_FILES as $file) {
            $disk->put($this->cdnSubFolderUuid . "/" . $file, \Faker\Factory::create()->text(500));
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
     * Delete created files
     */
    public function tearDown()
    {
        $disk = Storage::disk("cdn");

        // Clean files except .gitignore
        foreach ($disk->allFiles($this->cdnSubFolderUuid) as $file) {
            if ($file !== ".gitignore") {
                $disk->delete($file);
            }
        }

        // Clean directories
        foreach ($disk->directories($this->cdnSubFolderUuid) as $directory) {
            $disk->deleteDirectory($directory);
        }

        $disk->deleteDirectory($this->cdnSubFolderUuid);
    }

    /**
     * Get a random cdn directory
     *
     * @return string
     */
    public function randomDirectory()
    {
        return $this->faker->randomElement(self::getDisk()->allDirectories($this->cdnSubFolderUuid));
    }

    /**
     * Get a random cdn file
     *
     * @return mixed
     */
    public function randomFile()
    {
        return $this->faker->randomElement(self::getDisk()->allFiles($this->cdnSubFolderUuid));
    }

    /**
     * GET /files/{path}
     *
     * path: an existing file
     * Assert HTTP 200 success
     */
    public function testInfoFile()
    {
        $this->json("get", "/files/{$this->randomFile()}");

        $this->assertHttp401Unauthorized();
    }

    /**
     * GET /files/{path}
     *
     * path: an existing file
     * Assert HTTP 200 success
     */
    public function testInfoDirectory()
    {
        $this->json("get", "/files/{$this->randomDirectory()}");

        $this->assertHttp401Unauthorized();
    }

    /**
     * GET /files/explore/{directory}
     *
     * directory: existing directory on the CDN
     * Assert HTTP 200 success
     */
    public function testExplore()
    {
        $this->json("get", "/files/explore/{$this->randomDirectory()}");

        $this->assertHttp401Unauthorized();
    }

    /**
     * GET /files/explore/{directory}
     *
     * directory: not existing directory on the CDN
     * Assert HTTP 404 file_not_found
     */
    public function testExploreNonExistingDirectory()
    {
        $this->json("get", "/files/explore/idontexists");

        $this->assertHttp401Unauthorized();
    }

    /**
     * GET /files/explore/{directory}
     *
     * directory: existing file
     * Assert HTT 400 invalid_exception
     */
    public function testExploreFile()
    {
        $this->json("get", "/files/explore/{$this->randomFile()}");

        $this->assertHttp401Unauthorized();
    }

    /**
     * POST /content/upload
     * Test file uploads on CDN with non-existing file.
     */
    public function testUpload()
    {
        $this->call("post", "/files", [
            "file" => UploadedFile::fake()->image("avatar.jpg"),
            "destination" => $this->cdnSubFolderUuid . "/test",
            "name" => "avatar.jpg"
        ]);

        $this->assertHttp401Unauthorized();
    }

    /**
     * POST /content/upload
     * Test file uploads on CDN with existing file with force set to true.
     *
     * Assert HTTP 401 unauthorized
     */
    public function testUploadExistingForce()
    {
        $existingFile = $this->randomFile();
        $name = basename($existingFile);
        $destination = substr(str_replace($name, "", $existingFile), 0, -1);

        $this->call("post", "/files", [
            "file" => UploadedFile::fake()->image("avatar.jpg"),
            "destination" => $destination,
            "name" => $name,
            "force" => true
        ]);

        $this->assertHttp401Unauthorized();
    }

    /**
     * POST /content/upload
     * Test file uploads on CDN with existing file with force set to false.
     */
    public function testUploadExistingNoForce()
    {
        $existingFile = $this->randomFile();
        $name = basename($existingFile);
        $destination = substr(str_replace($name, "", $existingFile), 0, -1);

        $this->call("post", "/files", [
            "file" => UploadedFile::fake()->image("avatar.jpg"),
            "destination" => $destination,
            "name" => $name,
            "force" => false
        ]);

        $this->assertHttp401Unauthorized();
    }

    /**
     * PATCH /files/{file}
     * Rename a file
     *
     * file: existing file
     * Assert HTT 400 invalid_exception
     */
    public function testRenameFile()
    {
        $source = $this->randomFile();
        $destination = "$source.new";

        $this->json("patch", "/files/$source", [
            "destination" => $destination
        ]);

        $this->assertHttp401Unauthorized();
    }

    /**
     * PATCH /files/{file}
     * Rename a file to an existing destination file, without force
     *
     * file: existing file
     * Assert HTT 400 invalid_exception
     */
    public function testRenameExistingFileNoForce()
    {
        $source = $this->randomFile();
        $destination = ".gitignore";

        $this->json("patch", "/files/$source", [
            "destination" => $destination,
            "force" => false
        ]);

        $this->assertHttp401Unauthorized();
    }

    /**
     * PATCH /files/{file}
     * Rename a file to an existing destination file, with force
     *
     * file: existing file
     * Assert HTTP 401 unauthorized
     */
    public function testRenameExistingFileForce()
    {
        $source = $this->randomFile();
        do {
            $destination = $this->randomFile();
        } while ($destination === $source);

        $this->json("patch", "/files/$source", [
            "destination" => $destination,
            "force" => true
        ]);

        $this->assertHttp401Unauthorized();
    }

    /**
     * DELETE /files/{file}
     * Delete a file (send it to trash)
     *
     * file: existing file
     * Assert HTTP 200 success
     */
    public function testDeleteFile()
    {
        $file = $this->randomFile();

        $this->json("delete", "/files/$file");

        $this->assertHttp401Unauthorized();
    }

    /**
     * DELETE /files/{file}
     * Delete a file which does not exist
     *
     * file: non existing file
     * Assert HTTP 404 file_not_found
     */
    public function testDeleteNotExistingFile()
    {
        $this->json("delete", "/files/idontexist");

        $this->assertHttp401Unauthorized();
    }

    /**
     * DELETE /files/{file}
     * Delete a file with force (completely delete it)
     *
     * file: existing file
     * Assert HTTP 401 unauthorized
     */
    public function testDeleteFileForce()
    {
        $file = $this->randomFile();

        $this->json("delete", "/files/$file", [
            "force" => true
        ]);

        $this->assertHttp401Unauthorized();
    }
}
