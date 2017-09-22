<?php
namespace Tests\Moderator\Traits;

use App\Models\Notion;

/**
 * Trait NotionsDesc
 * Test notions for moderator and lower
 *
 * @package Tests\Moderator\Traits
 */
trait NotionsDesc
{
    /*
    |--------------------------------------------------------------------------
    | POST /notions
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 401 unauthorized
     */
    public function testAddUnauthorized()
    {
        $this->call('post', '/notions', factory(Notion::class)->raw());

        // Assertions
        $this->assertJsonError('unauthorized', 401);
    }


    /*
    |--------------------------------------------------------------------------
    | PATCH /notions/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 401 unauthorized
     */
    public function testEditUnauthorized()
    {
        $notion = Notion::inRandomOrder()->first();
        $this->call('patch', '/notions/' . $notion->id, factory(Notion::class)->raw());

        // Assertions
        $this->assertJsonError('unauthorized', 401);
    }
}