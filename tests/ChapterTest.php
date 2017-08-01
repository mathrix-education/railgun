<?php

use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Notion;

/**
 * Class ContentTest
 * @property Country $country
 * @property Class_ $class
 * @property Discipline $discipline
 * @property Chapter $chapter
 * @property Notion $notion
 */
class ContentTest extends BaseTest
{
    public $country;
    public $class;
    public $discipline;
    public $chapter;
    public $notion;

    public function setUp()
    {
        parent::setUp();

        $this->country = Country::where('slug_short', 'fr')->firstOrFail();
        $this->class = $this->country
            ->classes()
            ->getResults()
            ->random();
        $this->discipline = $this->class
            ->disciplines()
            ->getResults()
            ->random();
        $this->chapter = $this->discipline
            ->chapters()
            ->where('class_id', $this->class->id)
            ->getResults()
            ->random();
        $this->notion = $this->chapter
            ->notions()
            ->getResults()
            ->random();
    }


    /*
    |--------------------------------------------------------------------------
    | Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test chapter index
     */
    public function testClassesIndex()
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
     * Test chapter index with country not found
     */
    public function testIndexCountryNotFound()
    {
        $this->call(
            'GET',
            '/xmv' .
            '/' . $this->class->slug_short .
            '/' . $this->discipline->slug
        );

        $this->assertResponseStatus(404);
    }

    /**
     * Test chapter index with class not found
     */
    public function testIndexClassNotFound()
    {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/xmv' .
            '/' . $this->discipline->slug
        );

        $this->assertResponseStatus(404);
    }

    /**
     * Test chapter index with discipline not found
     */
    public function testIndexDisciplineNotFound()
    {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/' . $this->class->slug_short .
            '/xmv'
        );

        $this->assertResponseStatus(404);
    }
}