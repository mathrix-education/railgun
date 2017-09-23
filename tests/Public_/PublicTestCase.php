<?php
namespace Tests\Public_;

use Tests\PermissionTestCase;

/**
 * Class PublicTestCase
 * Base class for public tests
 *
 * @author Mathrix Team
 * @package Tests\Public_
 */
abstract class PublicTestCase extends PermissionTestCase
{
    /**
     * Don't initialize any user
     *
     * @return null
     */
    public function initUser()
    {
        return null;
    }
}