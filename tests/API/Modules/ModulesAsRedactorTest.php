<?php

namespace Tests\API\Modules;

/**
 * Class ModulesAsModeratorTest
 * Test modules as moderator.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
 */
class ModulesAsRedactorTest extends ModulesAsRegisteredTest
{
    /**
     * PATCH /modules/{id}.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEdit();
        $this->assertHttp200Success();
    }
}
