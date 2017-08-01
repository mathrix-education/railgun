<?php
namespace App\Tests;

use App\Models\Notion;

/**
 * Class NotionTest
 * Test Notion Model
 * @author Mathrix Team
 * @see Notion
 */
class NotionTest extends ContentBaseTest
{
    /**
     * Unit test setup
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->initChapter(); // Requires a chapter
    }

    /*
    |--------------------------------------------------------------------------
    | Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test notions index
     */
    public function testNotionsIndex(): void
    {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/' . $this->class->slug_short .
            '/' . $this->discipline->slug .
            '/' . $this->chapter->slug . '-' . $this->chapter->id
        );

        $this->assertResponseOk();
    }

    /**
     * Test notions index with non-existing Chapter::id
     */
    public function testNotionsIndexNotFound(): void
    {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/' . $this->class->slug_short .
            '/' . $this->discipline->slug .
            '/' . $this->chapter->slug . '-999999999'
        );

        $this->assertJsonError('model_not_found', 404);
    }
}