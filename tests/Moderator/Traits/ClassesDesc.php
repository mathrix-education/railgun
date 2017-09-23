<?php
namespace Tests\Moderator\Traits;

use App\Models\Class_;

/**
 * Trait ClassesDesc
 * Test classes for moderator and lower
 *
 * @author Mathrix Team
 * @package Tests\Moderator\Traits
 */
trait ClassesDesc
{
    /*
    |--------------------------------------------------------------------------
    | POST /classes
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 401 unauthorized
     */
    public function testAddUnauthorized()
    {
        $this->call('post', '/classes', factory(Class_::class)->raw());

        // Assertions
        $this->assertJsonError('unauthorized', 401);
    }


    /*
    |--------------------------------------------------------------------------
    | PATCH /classes/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 401 unauthorized
     */
    public function testEditUnauthorized()
    {
        $class = Class_::inRandomOrder()->first();
        $this->call('patch', '/classes/' . $class->id, factory(Class_::class)->raw());

        // Assertions
        $this->assertJsonError('unauthorized', 401);
    }
}