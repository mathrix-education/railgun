<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * Class APITestCase.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class APIStatelessTestCase extends APIStatefulTestCase
{
    use DatabaseTransactions;
}
