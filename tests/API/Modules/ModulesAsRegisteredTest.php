<?php

namespace Tests\API\Modules;

use App\Models\Module;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\Utils\Tree;

/**
 * Class ModulesAsPublicTest
 * Test modules as registered user.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
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
        $responseKeys = array_keys((array) $response);
        $expectedKeys = ["id", "premium"];
        $this->assertTrue($response->premium);
        $this->assertEquals($expectedKeys, $responseKeys);
    }

    /**
     * Generic mark test.
     *
     * @param bool|null $favorite
     * @param bool|null $to_revise
     * @param bool|null $completed
     *
     * @throws \Exception
     */
    public function mark($favorite, $to_revise, $completed)
    {
        $tree = new Tree();
        $data = $tree->flatIds();
        $moduleId = $data["module_id"];
        unset($data["module_id"]);

        if ($favorite !== null) {
            $data["favorite"] = $favorite;
        }
        if ($to_revise !== null) {
            $data["to_revise"] = $to_revise;
        }
        if ($completed !== null) {
            $data["completed"] = $completed;
        }

        $this->call("patch", "/modules/$moduleId/mark", $data);

        $expectedData = array_merge($data, ["user_id" => $this->user->id]);

        $this->assertHttp200Success();
        $this->assertInDatabase("metas", $expectedData);
    }

    /**
     * PATCH /modules/{id}/mark with {favorite: true}.
     *
     * @throws \Exception
     */
    public function testMarkFavorite()
    {
        $this->mark(true, null, null);
    }

    /**
     * PATCH /modules/{id}/mark with {to_revise: true}.
     *
     * @throws \Exception
     */
    public function testMarkToRevise()
    {
        $this->mark(null, true, null);
    }

    /**
     * PATCH /modules/{id}/mark with {completed: true}.
     *
     * @throws \Exception
     */
    public function testMarkCompleted()
    {
        $this->mark(null, null, true);
    }
}
