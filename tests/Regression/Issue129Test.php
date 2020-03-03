<?php

declare(strict_types=1);

namespace Tests\Regression;

use App\Models\Module;
use App\Models\User;
use Exception;
use Tests\APIStatelessTestCase;
use function factory;

/**
 * @link https://github.com/mathrix-education/education-api/issues/129
 */
class Issue129Test extends APIStatelessTestCase
{
    /**
     * @throws Exception
     *
     * @testdox assert that the deletion of a module which is in the user last_session does not raise an exception
     *          anymore.
     */
    public function testLastSessionDoesNotCrash(): void
    {
        /** @var Module $module */
        $module       = factory(Module::class)->create();
        $moduleId     = $module->id;
        $disciplineId = $module->activity->notion->chapter->discipline_id;

        /** @var User $user */
        $user = factory(User::class)->create([
            'settings' => [
                'last_session' => [$disciplineId => $moduleId],
            ],
        ]);

        $this->assertNotEmpty($user->last_session);

        $module->delete();
        $user->fresh();

        $this->assertEmpty($user->last_session);
    }
}
