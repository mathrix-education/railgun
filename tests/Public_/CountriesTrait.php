<?php
namespace Tests\Public_;

use App\Models\Country;

trait CountriesTrait
{
    public function testIndexSuccess() {
        $count = Country::all()->count();
        $this->call('get', '/countries');

        $this->assertResponseStatus(200);
        $this->assertJsonLength($count);
    }
}