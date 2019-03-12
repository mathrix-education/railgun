<?php

namespace Tests\API\Content;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Class ContentAsModeratorTest.
 * Test content as moderator.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Content
 */
class ContentAsModeratorTest extends ContentAsRegisteredTest
{
    /**
     * POST /content/upload
     * Test file uploads on CDN.
     */
    public function testUpload()
    {
        $this->call("post", "/content/upload", [
            "file" => UploadedFile::fake()->image("avatar.jpg"),
            "destination" => "test",
            "name" => "avatar.jpg",
        ]);

        $this->assertHttp200Success();
        Storage::disk("cdn")->assertExists("test/avatar.jpg");
    }
}
