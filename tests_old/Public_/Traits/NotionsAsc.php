<?php
namespace Tests\Public_\Traits;

use App\Models\Notion;

/**
 * Trait NotionsAsc
 * Test notions as public or higher
 *
 * @author Mathrix Team
 * @package Tests\Public_\Traits
 */
trait NotionsAsc
{
    /*
    |--------------------------------------------------------------------------
    | GET /notions
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testIndex()
    {
        $this->call('get', '/notions');

        $expectedNotionsCount = Notion::all()->count();

        // Assertions
        $this->assertResponseStatus(200);
        $this->assertJsonLength($expectedNotionsCount);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /notions/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testGet()
    {
        /** @var Notion $notion */
        $notion = Notion::inRandomOrder()->first();

        $this->call('get', '/notions/' . $notion->id);

        // Assertions
        $this->assertResponseStatus(200);
    }


    /**
     * Expect HTTP 404 model_not_found
     */
    public function testGetModelNotFound()
    {
        $this->call('get', '/notions/12345678');

        // Assertions
        $this->assertJsonError('model_not_found', 404);
    }
}