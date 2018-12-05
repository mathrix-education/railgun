<?php

namespace Tests\API\Files;

use App\Controllers\FileController;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Constraint\RegularExpression;

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
    /**
     * GET /files/{path}
     *
     * path: an existing file
     * Assert HTTP 200 success
     */
    public function testInfoFile()
    {
        $this->json("get", "/files/{$this->randomFile()}");

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), $this->fileSignature);
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

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), $this->directorySignature);
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

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatchesSchema($this->response->getContent(), [
            "[*].type" => "file",
            "[*].path" => new RegularExpression("/^[a-zA-Z0-9\-\_\/\.]+$/"),
            "[*].timestamp" => new IsType("integer"),
            "[*].url" => new IsType("string")
        ]);
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

        $this->assertHttpError(404, "file_not_found");
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

        $this->assertHttp400InvalidRequest();
    }

    /**
     * POST /content/upload
     * Test file uploads on CDN with non-existing file.
     */
    public function testUpload()
    {
        $this->call("post", "/files", [
            "file" => UploadedFile::fake()->image("avatar.jpg"),
            "destination" => "{$this->cdnSubFolderUuid}/test",
            "name" => "avatar.jpg"
        ]);

        $this->assertHttp200Success();
        self::getDisk()->assertExists("{$this->cdnSubFolderUuid}/test/avatar.jpg");
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

        $this->assertHttp400InvalidRequest();
        self::getDisk()->assertExists($existingFile);
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

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), $this->fileSignature);
        self::getDisk()->assertMissing($source);
        self::getDisk()->assertExists($destination);

        self::getDisk()->move($destination, $source); // Rollback
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

        $this->assertHttp400InvalidRequest();
        self::getDisk()->assertExists($source);
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

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), $this->fileSignature);
        self::getDisk()->assertExists(FileController::TRASH_DIR . "/$file");

        // Rollback
        self::getDisk()->move(FileController::TRASH_DIR . "/$file", $file);
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

        $this->assertHttpError(404, "file_not_found");
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
