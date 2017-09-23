<?php
namespace Tests\Public_\Traits;

use App\Models\Country;

/**
 * Trait CountriesAsc
 * Test countries as public
 *
 * @author Mathrix Team
 * @package Tests\Public_\Traits
 */
trait CountriesAsc
{
    public $jsonStructure = [
        'id',
        'name',
        'name_short',
        'slug',
        'slug_short'
    ];


    /**
     * Test GET /countries
     */
    public function testIndex()
    {
        $count = Country::all()->count();
        $this->call('get', '/countries');

        // Assertions
        $this->assertResponseStatus(200);
        $this->assertJsonLength($count);
    }


    /**
     * Test GET /countries/{id}
     */
    public function testGet()
    {
        $id = Country::inRandomOrder()->pluck('id')->first();
        $this->call('get', '/countries/' . $id);

        // Assertions
        $this->seeJsonStructure($this->jsonStructure);
    }
}