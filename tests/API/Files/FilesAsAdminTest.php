<?php
namespace Tests\API\Files;

use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Constraint\IsType;

/**
 * Class FilesAsAdminTest.
 * Test files as admin.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.1
 * @copyright Mathrix Education SA
 * @package   Tests\API\Files
 */
class FilesAsAdminTest extends FilesAsModeratorTest
{
    /**
     * POST /content/upload
     * Test file uploads on CDN with existing file with force set to true.
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

        $this->assertHttp200Success();
        self::getDisk()->assertExists($existingFile);
    }

    /**
     * GET /files/explore/{directory}
     *
     * directory: existing file
     * Assert HTT 400 invalid_exception
     */
    public function testRenameExistingFileForce()
    {
        $source = $this->randomFile();
        do {
            $destination = $this->randomFile();
        } while ($destination === $source);

        self::getDisk()->copy($destination, "$destination.old");

        $this->json("patch", "/files/$source", [
            "destination" => $destination,
            "force" => true
        ]);

        $this->assertJsonDocumentMatches($this->response->getContent(), $this->fileSignature);
        self::getDisk()->assertMissing($source);
        self::getDisk()->assertExists($destination);

        // Rollback
        self::getDisk()->move($destination, $source);
        self::getDisk()->move("$destination.old", $destination);
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

        self::getDisk()->copy($file, "$file.old");

        $this->json("delete", "/files/$file", [
            "force" => true
        ]);

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "status" => "success",
            "message" => new IsType("string")
        ]);
        self::getDisk()->assertMissing($file);

        // Rollback
        self::getDisk()->move("$file.old", $file);
    }
}
