<?php
namespace Tests\Public_\Traits;

use App\Models\Class_;

/**
 * Trait ClassesAsc
 * Test class as public or higher
 *
 * @package Tests\Public_\Traits
 */
trait ClassesAsc
{
    /*
    |--------------------------------------------------------------------------
    | GET /classes
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testIndex()
    {
        $this->call('get', '/classes');

        $expectedClassesCount = Class_::all()->count();

        // Assertions
        $this->assertResponseStatus(200);
        $this->assertJsonLength($expectedClassesCount);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /classes/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testGet()
    {
        /** @var Class_ $class */
        $class = Class_::inRandomOrder()->first();

        $this->call('get', '/classes/' . $class->id);

        // Assertions
        $this->assertResponseStatus(200);
    }


    /**
     * Expect HTTP 404 model_not_found
     */
    public function testGetModelNotFound()
    {
        $this->call('get', '/classes/404');

        // Assertions
        $this->assertJsonError('model_not_found', 404);
    }
}