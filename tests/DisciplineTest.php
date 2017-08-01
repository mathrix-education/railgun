<?php
namespace App\Tests;

use App\Models\Discipline;

/**
 * Class DisciplineTest
 * Test Discipline Model
 * @author Mathrix Team
 * @see Discipline
 */
class DisciplineTest extends ContentBaseTest
{
    /**
     * Unit test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->initClass(); // Requires a class
    }

    /*
    |--------------------------------------------------------------------------
    | Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test disciplines index
     */
    public function testDisciplinesIndex(): void
    {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/' . $this->class->slug_short
        );

        $this->assertResponseOk();
    }

    /**
     * Test disciplines index with non-existing Class_::shortSlug
     */
    public function testDisciplinesIndexNotFound(): void
    {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/xmv'
        );

        $this->assertJsonError('model_not_found', 404);
    }
}