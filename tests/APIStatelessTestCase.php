<?php

declare(strict_types=1);

namespace Tests;

use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * Base class for stateless (with no database persistence) tests.
 */
class APIStatelessTestCase extends APIStatefulTestCase
{
    use DatabaseTransactions;
}
