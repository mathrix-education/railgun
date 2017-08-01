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

    /**
     * Setup the tests
     */
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
     * Test classes index
     */
    public function testClassesIndex()
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
    public function testClassesIndexNotFound()
    {
        $this->call(
            'GET',
            '/xmv'
        );

        $this->assertJsonError('model_not_found', 404);
    }

    /**
     * Test disciplines index
     */
    public function testDisciplinesIndex() {
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
    public function testDisciplinesIndexNotFound()
    {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/xmv'
        );

        $this->assertJsonError('model_not_found', 404);
    }

    /**
     * Test chapters index
     */
    public function testChaptersIndex() {
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
    public function testChaptersIndexNotFound()
    {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/' . $this->class->slug_short .
            '/xmv'
        );

        $this->assertJsonError('model_not_found', 404);
    }

    /**
     * Test notions index
     */
    public function testNotionsIndex() {
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
    public function testNotionsIndexNotFound() {
        $this->call(
            'GET',
            '/' . $this->country->slug_short .
            '/' . $this->class->slug_short .
            '/' . $this->discipline->slug .
            '/' . $this->chapter->slug . '-999999999'
        );

        $this->assertJsonError('model_not_found', 404);
    }

    /**
     * Test activities index
     */
    public function testActivitiesIndex() {
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
    public function testActivitiesIndexNotFound() {
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