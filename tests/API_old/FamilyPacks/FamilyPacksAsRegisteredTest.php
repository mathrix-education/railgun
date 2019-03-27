<?php

namespace Tests\API_old\FamilyPacks;

use App\Models\FamilyPack;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Constraint\IsType;

/**
 * Class FamilyPacksAsRegisteredTest.
 * Test family packs as registered user.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.1
 * @copyright Mathrix Education SA
 * @package   Tests\API\FamilyPacks
 */
class FamilyPacksAsRegisteredTest extends FamilyPacksAsPublicTest
{
    /**
     * GET /family-packs/{familyPackId}
     */
    public function testGet()
    {
        $familyPack = FamilyPack::random();
        Passport::actingAs($familyPack->parent, [$this->user->type]);

        $this->json("get", "/family-packs/{$familyPack->id}");

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "$.id" => new IsType("integer"),
            "$.children_count" => new IsType("integer"),
            "$.parent_id" => new IsType("integer"),
            "$.created_at" => new IsType("string"),
            "$.updated_at" => new IsType("string"),
            "$.parent.id" => new IsType("integer"),
            "$.children" => new IsType("array"),
        ]);
    }
}
