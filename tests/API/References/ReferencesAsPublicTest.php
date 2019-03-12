<?php

namespace Tests\API\References;

use App\Models\Reference;
use PHPUnit\Framework\Constraint\IsType;
use Tests\API\ScopeTestCase;

/**
 * Class ReferencesAsPublicTest.
 * Test references as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Console\Commands\references
 */
class ReferencesAsPublicTest extends ScopeTestCase
{
    /**
     * GET /quotes.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * GET /quotes/{id}.
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();
    }


    /**
     * POST /quotes -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /quotes/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /quotes/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }


    /**
     * GET /quotes/random.
     */
    public function testQuery()
    {
        $referencesKeys = Reference::inRandomOrder()
            ->limit(5)
            ->get()
            ->pluck("key")
            ->toArray();

        $referencesKeys[1] .= "%";
        $referencesKeys[2] = "%$referencesKeys[2]";
        $query = implode(",", $referencesKeys);

        $this->json("get", "/references/query/$query");

        // Assertions
        $this->assertHttp200Success();
        $this->assertIsPaginatedResponse(1, 100);
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "data.[*].id" => new IsType("integer"),
            "data.[*].key" => new IsType("string"),
            "data.[*].text" => new IsType("string"),
            "data.[*].module_id" => new IsType("integer"),
        ]);
    }
}
