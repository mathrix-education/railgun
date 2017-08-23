<?php
namespace Tests\Moderator\Traits;

use App\Models\Country;

/**
 * Trait CountriesDesc
 * Test countries as moderator or lower
 *
 * @package Tests\Moderator\Traits
 */
trait CountriesDesc
{
    /*
    |--------------------------------------------------------------------------
    | POST /countries
    |--------------------------------------------------------------------------
    */
    /**
     * Expect 401 unauthorized
     */
    public function testAddUnauthorizedError()
    {
        $newData = factory(Country::class)->raw();

        $this->call('post', '/countries', $newData);

        // Assertions
        $this->assertJsonError('unauthorized', 401);
        $this->notSeeInDatabase('countries', $newData);
    }


    /*
    |--------------------------------------------------------------------------
    | PATCH /countries/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Expect 401 unauthorized
     */
    public function testEditUnauthorizedError()
    {
        /** @var Country $country */
        $country = Country::inRandomOrder()->get(['id'])->first();
        $newData = factory(Country::class)->raw();
        $this->call('patch', '/countries/' . $country->id, $newData);

        // Assertions
        $this->assertJsonError('unauthorized', 401);
        $this->notSeeInDatabase('countries', $newData);
    }
}