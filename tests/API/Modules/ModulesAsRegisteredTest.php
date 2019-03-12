<?php

namespace Tests\API\Modules;

use App\Models\Module;
use App\Models\Path;
use App\Models\User;
use App\Utils\Tree;
use Laravel\Passport\Passport;

/**
 * Class ModulesAsRegisteredTest.
 * Test modules as registered user.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Modules
 */
class ModulesAsRegisteredTest extends ModulesAsPublicTest
{
    /**
     * GET /modules/{id} with a premium module as non premium user.
     *
     * @throws \Exception
     */
    public function testGetPremiumAsNonPremium()
    {
        $user = User::whereNull("braintree_id")->whereType($this->user->type)->firstOrFail();
        Passport::actingAs($user, [$user->type]);

        /** @var Module $module */
        $module = Module::wherePremium(true)->inRandomOrder()->firstOrFail();
        $this->get("/modules/{$module->id}");

        $response = $this->getResponseData();
        $responseKeys = array_keys((array)$response);
        $expectedKeys = ["id", "premium"];
        $this->assertTrue($response->premium);
        $this->assertEquals($expectedKeys, $responseKeys);
    }


    /**
     * PATCH /modules/{id}/mark with {status: view}.
     *
     * @throws \Exception
     */
    public function testMarkView()
    {
        $this->mark("view");
    }


    /**
     * Generic mark test.
     *
     * @param $status
     *
     * @throws \Exception
     */
    public function mark($status)
    {
        do {
            $tree = new Tree();
        } while ($tree->getModule()->premium);

        $data = [
            "path" => $tree->flatIds(),
            "status" => $status
        ];
        $moduleId = $tree->getModule()->id;

        $this->json("patch", "/modules/$moduleId/mark", $data);

        $path = Path::retrieve($tree->flatIds());

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "id" => $moduleId,
            "mark" => $status
        ]);
        $this->assertInDatabase("marks", [
            "path_id" => $path->id,
            "user_id" => $this->user->id,
            "status" => $status
        ]);
    }


    /**
     * PATCH /modules/{id}/mark with {status: to_to_reviserevise}.
     *
     * @throws \Exception
     */
    public function testMarkToRevise()
    {
        $this->mark("to_revise");
    }


    /**
     * PATCH /modules/{id}/mark with {status: completed}.
     *
     * @throws \Exception
     */
    public function testMarkCompleted()
    {
        $this->mark("completed");
    }
}
