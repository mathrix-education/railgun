<?php
namespace Tests\Public_\Traits;

use App\Models\Country;

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
    public function testIndexSuccess() {
        $count = Country::all()->count();
        $this->call('get', '/countries');

        // Assertions
        $this->assertResponseStatus(200);
        $this->assertJsonLength($count);
    }

    /**
     * Test GET /countries/{id}
     */
    public function testGetSuccess() {
        $id = Country::inRandomOrder()->pluck('id')->first();
        $this->call('get', '/countries/' . $id);

        // Assertions
        $this->seeJsonStructure($this->jsonStructure);
    }
}