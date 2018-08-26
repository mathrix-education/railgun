<?php
namespace Tests\API;

use App\Models\Chapter;
use Tests\BaseTestCase;

class ChainTest extends BaseTestCase
{
    public function testChain()
    {
        $chapter = Chapter::random();
        $discipline = $chapter->discipline;
        $class = $chapter->class_;

        $data = [
            "requests" => [
                "discipline" => [
                    "method" => "get",
                    "url" => "/disciplines/slug/{$discipline->slug}",
                ],
                "chapter" => [
                    "method" => "get",
                    "url" => "/chapters/slug/{$chapter->slug}/by-class-slug/{$class->slug}",
                ],
                "notions" => [
                    "method" => "get",
                    "url" => "/notions/by-chapter/{chapter.id}",
                ],
            ],
        ];

        $this->json("post", "chain", $data);

        $this->debug();

        $responseKeys = array_keys($data["requests"]);
        $this->assertHttp200Success();
        $this->assertJsonKeys($responseKeys);
    }
}
