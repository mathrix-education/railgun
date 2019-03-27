<?php

namespace Tests\API_old\FamilyPacks;

use App\Models\FamilyPack;
use Tests\API_old\ScopeTestCase;

/**
 * Class FamilyPacksAsPublicTest.
 * Test family packs as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.1
 * @copyright Mathrix Education SA
 * @package   Tests\API\FamilyPacks
 */
class FamilyPacksAsPublicTest extends ScopeTestCase
{
    /**
     * GET /family-packs/{familyPackId}
     */
    public function testGet()
    {
        $familyPack = FamilyPack::random();

        $this->json("get", "/family-packs/{$familyPack->id}");

        $this->assertHttp401Unauthorized();
    }
}
