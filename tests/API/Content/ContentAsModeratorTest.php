<?php
namespace Tests\API\Content;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


/**
 * Class ContentAsModeratorTest
 * Test content as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package Tests\Content
 * @since
 * @copyright Mathrix Education SA
 */
class ContentAsModeratorTest extends ContentAsRegisteredTest
{
    /**
     * POST /content/upload
     * Test file uploads on CDN
     */
    public function testUpload()
    {
        $this->call("post", "/content/upload", [
            "file" => UploadedFile::fake()->image("avatar.jpg"),
            "destination" => "test",
            "name" => "avatar.jpg"
        ]);

        $this->assertHttp200Success();
        Storage::disk("cdn")->assertExists("test/avatar.jpg");
    }
}
