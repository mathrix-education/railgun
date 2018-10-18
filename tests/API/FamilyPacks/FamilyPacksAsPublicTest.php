<?php

namespace Tests\API\FamilyPacks;

use App\Models\FamilyPack;
use Tests\API\ScopeTestCase;

/**
 * Class FamilyPacksAsPublicTest
 * Test family packs as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   4.1.1
 */
class FamilyPacksAsPublicTest extends ScopeTestCase
{    /**
 * GET /family-packs/{familyPackId}
 */
    public function testGet()
    {
        $familyPack = FamilyPack::random();

        $this->json("get", "/family-packs/{$familyPack->id}");

        $this->assertHttp401Unauthorized();
    }
}
