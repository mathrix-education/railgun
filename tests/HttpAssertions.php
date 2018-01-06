<?php
namespace Tests;

/**
 * Trait HttpAssertions
 * Some shortcuts for HTTP codes & errors
 *
 * @package Tests
 *
 * @method assertHttp400InvalidRequest()
 * @method assertHttp401Unauthorized()
 * @method assertHttp501NotImplemented()
 */
trait HttpAssertions
{
    /**
     * Shortcut to assertResponseStatus(200)
     */
    public function assertHttp200Success()
    {
        $this->assertResponseStatus(200);
    }


    public function __call($name, $arguments)
    {
        $strCleaned = str_replace("assertHttp", "", $name);
        $errorCode = substr($strCleaned, 0, 3);
        $errorName = str_replace($errorCode, "", $strCleaned);
        $errorName = snake_case($errorName);
        $this->assertJsonError($errorName, $errorCode);
    }
}
