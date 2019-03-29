<?php

namespace Tests\API;

use Mathrix\Lumen\Tests\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\Standard\StandardPostTest;

/**
 * Class ModulesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ModulesTest extends APITestCase
{
    use StandardIndexTest, StandardGetTest, StandardPostTest, StandardPatchTest;
}
