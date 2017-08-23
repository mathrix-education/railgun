<?php
namespace Tests\Admin;

use App\Models\Country;
use Tests\Moderator\Traits\CountriesAsc;

class ClassesTest extends AdminTestCase
{
    use CountriesAsc;

    /*
    |--------------------------------------------------------------------------
    | POST /countries
    |--------------------------------------------------------------------------
    */
    /**
     * Test POST /countries
     * Expect HTTP 200 success
     */
    public function testAddSuccess()
    {
        $newData = factory(Country::class)->raw();

        $this->call('post', '/countries', $newData);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('countries', $newData);
    }


    /**
     * Test POST /countries without slug, name_short nor slug_short
     * Expect HTTP 200 success
     */
    public function testAddOnlyNameSuccess()
    {
        $name = $this->faker->firstName;
        $expectedData = [
            'name' => $name,
            'slug' => str_slug($name),
            'name_short' => $name,
            'slug_short' => str_slug($name)
        ];

        $this->call('post', '/countries', [
            'name' => $name
        ]);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('countries', $expectedData);
    }


    /**
     * Test POST /countries with slug, name_short and slug_short but withou name
     * Expect HTTP 422 validation
     */
    public function testAddWithoutNameSuccess()
    {
        $newData = factory(Country::class)->raw();

        $this->call('post', '/countries', array_diff_key($newData, ['name' => 'name']));

        // Assertions
        $this->assertResponseStatus(422);
        $this->notSeeInDatabase('countries', $newData);
    }


    /*
    |--------------------------------------------------------------------------
    | PATCH /countries/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Test PATCH /countries/{id}
     * Expect HTTP 200 success
     */
    public function testEditSuccess()
    {
        /** @var Country $country */
        $country = Country::inRandomOrder()->get(['id'])->first();
        $newData = factory(Country::class)->raw();
        $this->call('patch', '/countries/' . $country->id, $newData);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('countries', $newData);
    }


    /**
     * Test PATCH /countries/{id} with only name
     * Expect HTTP 200 success
     */
    public function testEditOnlyNameSuccess()
    {
        /** @var Country $country */
        $country = Country::inRandomOrder()->get(['id'])->first();
        $name = $this->faker->firstName;
        $expectedData = [
            'name' => $name,
            'slug' => str_slug($name),
            'name_short' => $name,
            'slug_short' => str_slug($name)
        ];

        $this->call('patch', '/countries/' . $country->id, ['name' => $name]);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('countries', $expectedData);
    }


    /**
     * Test PATCH /countries/{id} without name
     * Expect HTTP 200 success
     */
    public function testEditWithoutNameSuccess()
    {
        /** @var Country $country */
        $country = Country::inRandomOrder()->get(['id'])->first();
        $name = $this->faker->firstName;
        $newData = [
            'slug' => str_slug($name),
            'name_short' => $name,
            'slug_short' => str_slug($name)
        ];

        $this->call('patch', '/countries/' . $country->id, $newData);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('countries', $newData);
    }
}