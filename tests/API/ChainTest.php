<?php

namespace Tests\API;

use App\Models\Chapter;

class ChainTest extends APITestCase
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

        $responseKeys = array_keys($data["requests"]);
        $this->assertHttp200Success();
        $this->assertJsonKeys($responseKeys);
    }

    public function testChainWithPost()
    {
        $chapterData = factory(Chapter::class)->raw();

        $this->call("post", "/chain", [
            "requests" => [
                "chapter_creation" => [
                    "method" => "post",
                    "url" => "/chapters",
                    "data" => $chapterData
                ],
                "chapter" => [
                    "method" => "get",
                    "url" => "/chapters/{chapter_creation.id}"
                ]
            ]
        ]);

        $this->assertHttp200Success();
        $this->debug();
    }
}
