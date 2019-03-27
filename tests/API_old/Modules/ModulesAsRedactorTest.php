<?php

namespace Tests\API_old\Modules;

/**
 * Class ModulesAsRedactorTest.
 * Test modules as redactor.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Modules
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
