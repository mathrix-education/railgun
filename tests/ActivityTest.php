<?php
namespace App\Tests;

use App\Models\Activity;

/**
 * Class NotionTest
 * Test Notion Model
 * @author Mathrix Team
 * @see Activity
 */
class ActivityTest extends ContentBaseTest
{
    /**
     * Unit test setup
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->initNotion(); // Requires a notion
    }

    /*
    |--------------------------------------------------------------------------
    | Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test activities index
     */
    public function testActivitiesIndex(): void {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/' . $this->class->slug_short .
            '/' . $this->discipline->slug .
            '/' . $this->chapter->slug . '-' . $this->chapter->id .
            '/' . $this->notion->slug . '-' . $this->notion->id
        );

        $this->assertResponseOk();
    }

    /**
     * Test activities index with non-existing Notion::id
     */
    public function testActivitiesIndexNotFound(): void {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/' . $this->class->slug_short .
            '/' . $this->discipline->slug .
            '/' . $this->chapter->slug . '-' . $this->chapter->id .
            '/' . $this->notion->slug . '-999999999'
        );

        $this->assertJsonError('model_not_found', 404);
    }
}