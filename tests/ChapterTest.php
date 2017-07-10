<?php

use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;

class ChapterTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | Common
    |--------------------------------------------------------------------------
    |
    | Common variables and methods
    |
    */
    /**
     * @var Country
     */
    private $_country;
    /**
     * @var Class_|null
     */
    private $_class;
    /**
     * @var Discipline|null
     */
    private $_discipline;

    /**
     * Get a random country
     * @return Country
     */
    private function _country()
    {

        if (is_null($this->_country)) {
            $this->_country = Country::where('slug_short', 'fr')->first();
        }

        return $this->_country;
    }

    /**
     * Get a random class
     * @return Class_
     */
    private function _class()
    {
        if (is_null($this->_class)) {
            $this->_class = Class_::inRandomOrder()->first();
        }

        return $this->_class;
    }

    /**
     * Get a random discipline
     * @return Discipline
     */
    private function _discipline()
    {

        if (is_null($this->_discipline)) {
            $this->_discipline = Discipline::inRandomOrder()->first();
        }

        return $this->_discipline;
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
            '/' . $this->_country()->slug_short .
            '/' . $this->_class()->slug_short .
            '/' . $this->_discipline()->slug
        );

        $this->assertResponseOk();
    }

    /**
     * Test chapter index with country not found
     */
    public function testIndexCountryNotFound()
    {
        $this->json(
            'GET',
            '/xmv' .
            '/' . $this->_class()->slug_short .
            '/' . $this->_discipline()->slug
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
            '/' . $this->_country()->slug_short .
            '/xmv' .
            '/' . $this->_discipline()->slug
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
            '/' . $this->_country()->slug_short .
            '/' . $this->_class()->slug_short .
            '/xmv'
        );

        $this->assertResponseStatus(404);
    }

}