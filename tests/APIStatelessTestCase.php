<?php

declare(strict_types=1);

namespace Tests;

use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * Base class for stateless (with no database persistence) tests.
 * To do so, we use the Laravel Database Transaction trait.
 *
 * @link https://laravel.com/docs/5.8/database
 * @see  DatabaseTransactions
 */
class APIStatelessTestCase extends APIStatefulTestCase
{
    use DatabaseTransactions;
}
