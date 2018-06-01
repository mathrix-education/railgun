<?php
namespace Tests\Traits;

/**
 * Trait HttpAssertionsTrait
 * Some shortcuts for HTTP codes & errors
 *
 * @package Tests
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 4.0.3
 * @package Tests\Votes
 *
 * @method assertHttp400InvalidRequest(): $this
 * @method assertHttp401Unauthorized(): $this
 * @method assertHttp404ModelNotFound(): $this
 * @method assertHttp422Validation(): $this
 * @method assertHttp429TooManyRequests(): $this
 * @method assertHttp501NotImplemented(): $this
 */
trait HttpAssertionsTrait
{
    /**
     * Shortcut to assertResponseStatus(200)
     */
    public function assertHttp200Success()
    {
        $this->assertResponseStatus(200);
        return $this;
    }


    public function __call($name, $arguments)
    {
        $strCleaned = str_replace("assertHttp", "", $name);
        $errorCode = substr($strCleaned, 0, 3);
        $errorName = str_replace($errorCode, "", $strCleaned);
        $errorName = snake_case($errorName);
        $this->assertJsonError($errorName, $errorCode);
        return $this;
    }
}
