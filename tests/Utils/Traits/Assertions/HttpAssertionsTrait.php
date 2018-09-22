<?php

namespace Tests\Utils\Traits\Assertions;

/**
 * Trait HttpAssertionsTrait
 * Some shortcuts for HTTP codes & errors.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 *
 * @since     4.0.3
 *
 * @copyright Mathrix Education SA
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
    public function __call($name, $arguments)
    {
        $strCleaned = str_replace("assertHttp", "", $name);
        $errorCode = mb_substr($strCleaned, 0, 3);
        $errorName = str_replace($errorCode, "", $strCleaned);
        $errorName = snake_case($errorName);
        $this->assertJsonError($errorName, $errorCode);

        return $this;
    }

    /**
     * Shortcut to assertResponseStatus(200).
     */
    public function assertHttp200Success()
    {
        $this->assertResponseStatus(200);

        return $this;
    }
}
