<?php
namespace Tests\Public_;

/**
 * Class VotesTest
 * Test votes as public
 *
 * @author Mathrix Team
 * @package Tests\Public_
 */
class VotesTest extends PublicTestCase
{
    /*
    |--------------------------------------------------------------------------
    | /votes/{commentable_type}/{commentable_id}/up
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 401 unauthorized
     */
    public function testUpUnauthorized()
    {
        $this->call('post', '/votes/comment/12/up');
        $this->assertJsonError('unauthorized', 401);
    }

    /*
    |--------------------------------------------------------------------------
    | /votes/{commentable_type}/{commentable_id}/down
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 401 unauthorized
     */
    public function testDownUnauthorized()
    {
        $this->call('post', '/votes/comment/12/up');
        $this->assertJsonError('unauthorized', 401);
    }
}