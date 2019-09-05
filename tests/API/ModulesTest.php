<?php

namespace Tests\API;

use App\Models\Module;
use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
use Tests\APIStatelessTestCase;

/**
 * Class ModulesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ModulesTest extends APIStatelessTestCase
{
    use RESTAutoTestTrait;

    protected $testKeys = [
        "std:index",
        "std:post",
        "std:get",
        "std:patch",
        "rel:get:comments",
        "rel:get:reports"
    ];


    /**
     * DELETE /modules/{moduleId}
     */
    public function testDelete(): void
    {
        $module = Module::random();
        $pivot = Module::random(["id", "!=", $module->id]);

        $module->posts()->update(["module_id" => $pivot->id]);
        $module->products()->update(["module_id" => $pivot->id]);

        $this->makeRESTJsonRequest("std:delete", null, null, [
            "conditions" => ["id", "=", $module->id]
        ]);
    }
}
