<?php
namespace Tests\Public_;

use Tests\PermissionTestCase;

/**
 * Class PublicTestCase
 * Base class for public tests
 *
 * @package Tests\Public_
 */
class PublicTestCase extends PermissionTestCase
{
    public function initUser()
    {
        return null;
    }
}