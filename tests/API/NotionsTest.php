<?php

namespace Tests\API;

use App\Models\Notion;
use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
use Tests\APIStatelessTestCase;

/**
 * Class NotionsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class NotionsTest extends APIStatelessTestCase
{
    use RESTAutoTestTrait;

    protected $testKeys = [
        "std:index",
        "std:post",
        "std:get",
        "std:patch"
    ];


    /**
     * DELETE /notions/{notionId}
     */
    public function testDelete(): void
    {
        $notion = Notion::random();
        $fallback = Notion::random(["id", "!=", $notion->id]);

        $notion->activities()->update(["notion_id" => $fallback->id]);

        $this->makeRESTJsonRequest("std:delete", null, null, [
            "conditions" => ["id", "=", $notion->id]
        ]);
    }
}
