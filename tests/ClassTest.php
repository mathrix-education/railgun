<?php
namespace App\Tests;

use App\Models\Class_;

/**
 * Class ClassTest
 * Test Class_ Model
 * @author Mathrix Team
 * @see Class_
 */
class ClassTest extends ContentBaseTest
{
    /**
     * Unit test setup
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->initCountry(); // Requires a country
    }

    /*
    |--------------------------------------------------------------------------
    | Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test classes index
     */
    public function testClassesIndex(): void
    {
        $this->call(
            'GET',
            '/' . $this->country->slug_short
        );

        $this->assertResponseOk();
    }

    /**
     * Test classes index with non-existing Country::shortSlug
     */
    public function testClassesIndexNotFound(): void
    {
        $this->call(
            'GET',
            '/xmv'
        );

        $this->assertJsonError('model_not_found', 404);
    }
}