<?php
namespace App\Tests;

use App\Models\Chapter;

/**
 * Class ChapterTest
 * Test Chapter Model
 * @author Mathrix Team
 * @see Chapter
 */
class ChapterTest extends ContentBaseTest
{
    /**
     * Unit test setup
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->initDiscipline(); // Requires a discipline
    }

    /*
    |--------------------------------------------------------------------------
    | Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test chapters index
     */
    public function testChaptersIndex(): void
    {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/' . $this->class->slug_short .
            '/' . $this->discipline->slug
        );

        $this->assertResponseOk();
    }

    /**
     * Test chapter index with non-existing Discipline::slug
     */
    public function testChaptersIndexNotFound(): void
    {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/' . $this->class->slug_short .
            '/xmv'
        );

        $this->assertJsonError('model_not_found', 404);
    }
}