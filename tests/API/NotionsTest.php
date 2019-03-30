<?php

namespace Tests\API;

use App\Models\Chapter;
use App\Models\Notion;
use Mathrix\Lumen\Tests\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\Standard\StandardPostTest;

/**
 * Class NotionsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class NotionsTest extends APITestCase
{
    use StandardIndexTest, StandardGetTest, StandardPostTest, StandardPatchTest;


    /**
     * DELETE /notions/{notionId}
     */
    public function testDelete()
    {
        $notion = Notion::random();
        $pivot = Notion::random(["id", "!=", $notion->id]);

        $notion->activities()->update(["notion_id" => $pivot->id]);

        $this->assertRestDeleteSuccess(["conditions" => ["id", "=", $notion->id]]);
    }
}
