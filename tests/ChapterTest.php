<?php

use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;

/**
 * Class ChapterTest
 *
 * @method Class_ randomClass_()
 * @method Discipline randomDiscipline()
 */
class ChapterTest extends BaseTest
{
    /**
     * Randomly retrieve a country
     * TODO: NB: It is currently biased because we only defined french classes
     * @return Country
     */
    public function randomCountry() {
        return Country::where('slug_short', 'fr')->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Tests
    |--------------------------------------------------------------------------
    |
    | Chapter tests
    |
    */
    /**
     * Test chapter index
     */
    public function testIndexOk()
    {
        $this->call(
            'GET',
            '/' . $this->randomCountry()->slug_short .
            '/' . $this->randomClass_()->slug_short .
            '/' . $this->randomDiscipline()->slug
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
            '/' . $this->randomClass_()->slug_short .
            '/' . $this->randomDiscipline()->slug
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
            '/' . $this->randomCountry()->slug_short .
            '/xmv' .
            '/' . $this->randomDiscipline()->slug
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
            '/' . $this->randomCountry()->slug_short .
            '/' . $this->randomClass_()->slug_short .
            '/xmv'
        );

        $this->assertResponseStatus(404);
    }
}