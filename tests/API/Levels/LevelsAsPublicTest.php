<?php
namespace Tests\API\Levels;

use Tests\API\ScopeTestCase;

/**
 * Class LevelsAsPublicTest.
 * Test levels as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.3
 * @copyright Mathrix Education SA
 * @package   Tests\API\Levels
 */
class LevelsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /levels.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }
}
