<?php

namespace Tests\API;

use App\Models\Chapter;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\Utils\Tree;

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
        $this->debug();
    }

    public function testChainWithPost()
    {
        $admin = User::where("type", "=", "admin")->firstOrFail();
        Passport::actingAs($admin, ["admin"]);

        $chapterData = factory(Chapter::class)->raw();

        $this->call("post", "/chain", [
            "requests" => [
                "chapter_creation" => [
                    "method" => "post",
                    "url" => "/chapters",
                    "data" => $chapterData
                ],
                "chapter_get" => [
                    "method" => "get",
                    "url" => "/chapters/{chapter_creation.id}"
                ]
            ]
        ]);

        $this->assertHttp200Success();
    }

    /**
     * @throws \Exception
     */
    public function testModuleDashboard()
    {
        $admin = User::where("type", "=", "admin")->firstOrFail();
        Passport::actingAs($admin, ["admin"]);

        $tree = new Tree();

        $this->call("post", "/chain", [
            "requests" => [
                "flat" => [
                    "method" => "post",
                    "url" => "/content/flat",
                    "data" => [
                        "country_slug" => $tree->getCountry()->slug,
                        "class_slug" => $tree->getClass()->slug,
                        "discipline_slug" => $tree->getDiscipline()->slug,
                        "chapter_slug" => $tree->getChapter()->slug,
                        "notion_slug" => $tree->getNotion()->slug,
                        "activity_slug" => $tree->getActivity()->slug,
                        "module_id" => $tree->getModule()->id
                    ]
                ],
                "comments" => [
                    "method" => "get",
                    "url" => "/comments/by-activity/{flat.activity.id}"
                ],
                "chapter_recursive" => [
                    "method" => "get",
                    "url" => "/chapters/recursive/{flat.chapter.id}"
                ]
            ]
        ]);

        $this->assertHttp200Success();
    }
}
