<?php
namespace Tests\Admin;

use App\Models\Country;
use Tests\Moderator\Traits\CountriesAsc;

class CountriesTest extends AdminTestCase
{
    use CountriesAsc;

    /*
    |--------------------------------------------------------------------------
    | POST /countries
    |--------------------------------------------------------------------------
    */
    /**
     * Test POST /countries with 200 success
     */
    public function testAddSuccess()
    {
        /** @var Country $country */
        $country = factory(Country::class)->make();

        $this->call('post', '/countries', $country->toArray());

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('countries', $country->toArray());
    }


    /**
     * Test POST /countries without slug, name_short nor slug_short
     * Expect HTTP 200 success
     */
    public function testAddSuccessWithoutSlug()
    {
        $name = $this->faker->firstName;

        $this->call('post', '/countries', [
            'name' => $name
        ]);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('countries', [
            'name' => $name,
            'slug' => str_slug($name),
            'name_short' => $name,
            'slug_short' => str_slug($name)
        ]);
    }
}