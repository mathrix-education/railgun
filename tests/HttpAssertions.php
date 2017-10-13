<?php
namespace Tests;

/**
 * Trait HttpAssertions
 * Some shortcuts for HTTP codes & errors
 *
 * @package Tests
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

    /*
     * $this->assertHttp4xxErrorName()
     * => $this->assertJsonError('errorName', 4xx)
     */
    /**
     * Shortcut to assertJsonError('unauthorized', 401)
     */
    public function assertHttp401Unauthorized()
    {
        $this->assertJsonError('unauthorized', 401);
    }

    /**
     * Shortcut to assertJsonError('not_implemented', 501)
     */
    public function assertHttp501NotImplemented()
    {
        $this->assertJsonError('not_implemented', 501);
    }
}