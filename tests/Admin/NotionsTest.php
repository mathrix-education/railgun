<?php
namespace Tests\Admin;

use App\Models\Country;
use Tests\Moderator\Traits\CountriesAsc;

/**
 * Class NotionsTest
 * Test classes as admin
 *
 * @package Tests\Admin
 */
class NotionsTest extends AdminTestCase
{
    use CountriesAsc;

    /*
    |--------------------------------------------------------------------------
    | POST /countries
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testAdd()
    {
        $newData = factory(Country::class)->raw();

        $this->call('post', '/countries', $newData);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('countries', $newData);
    }


    /**
     * Without slug, name_short nor slug_short
     * Expect HTTP 200 success
     */
    public function testAddOnlyName()
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
     * With slug, name_short and slug_short but without name
     * Expect HTTP 422 validation
     */
    public function testAddWithoutName()
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
     * Expect HTTP 200 success
     */
    public function testEdit()
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
     * With only name
     * Expect HTTP 200 success
     */
    public function testEditOnlyName()
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
     * Without name
     * Expect HTTP 200 success
     */
    public function testEditWithoutName()
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