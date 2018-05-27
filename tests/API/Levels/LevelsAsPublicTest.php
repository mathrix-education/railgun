<?php
namespace Tests\API\Levels;

use Tests\API\ScopeTestCase;

/**
 * Class LevelsAsPublicTest
 * Test levels as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 4.0.3
 * @package Tests\API\Levels
 */
class LevelsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /levels
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }
}
