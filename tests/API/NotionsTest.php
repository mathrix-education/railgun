<?php

namespace Tests\API;

use App\Models\Notion;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;
use Tests\APITestCase;

/**
 * Class NotionsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
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
