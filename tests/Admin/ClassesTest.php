<?php
namespace Tests\Admin;

use App\Models\Class_;
use Tests\Moderator\Traits\ClassesAsc;

/**
 * Class ClassesTest
 * Test classes as admin
 *
 * @author Mathrix Team
 * @package Tests\Admin
 */
class ClassesTest extends AdminTestCase
{
    use ClassesAsc;

    /*
    |--------------------------------------------------------------------------
    | POST /classes
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testAdd()
    {
        $newData = factory(Class_::class)->raw();

        $this->call('post', '/classes', $newData);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('classes', $newData);
    }


    /**
     * Without slug, name_short nor slug_short
     * Expect HTTP 200 success
     */
    public function testAddOnlyName()
    {

        $newData = factory(Class_::class)->raw();

        $expectedData = [
            'name' => $newData['name'],
            'slug' => str_slug($newData['name']),
            'name_short' => $newData['name'],
            'slug_short' => str_slug($newData['name'])
        ];

        $this->call('post', '/classes', [
            'name' => $newData['name'],
            'country_id' => $newData['country_id']
        ]);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('classes', $expectedData);
    }


    /**
     * With slug, name_short and slug_short but without name
     * Expect HTTP 422 validation
     */
    public function testAddWithoutName()
    {
        $newData = factory(Class_::class)->raw();

        $this->call('post', '/classes', array_diff_key($newData, ['name' => 'name']));

        // Assertions
        $this->assertResponseStatus(422);
        $this->notSeeInDatabase('classes', $newData);
    }


    /*
    |--------------------------------------------------------------------------
    | PATCH /classes/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testEdit()
    {
        /** @var Class_ $class */
        $class = Class_::inRandomOrder()->get(['id'])->first();
        $newData = factory(Class_::class)->raw();
        $this->call('patch', '/classes/' . $class->id, $newData);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('classes', $newData);
    }


    /**
     * With only name
     * Expect HTTP 200 success
     */
    public function testEditOnlyName()
    {
        /** @var Class_ $class */
        $class = Class_::inRandomOrder()->get(['id'])->first();
        $name = $this->faker->firstName;
        $expectedData = [
            'name' => $name,
            'slug' => str_slug($name),
            'name_short' => $name,
            'slug_short' => str_slug($name)
        ];

        $this->call('patch', '/classes/' . $class->id, ['name' => $name]);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('classes', $expectedData);
    }


    /**
     * Without name
     * Expect HTTP 200 success
     */
    public function testEditWithoutName()
    {
        /** @var Class_ $class */
        $class = Class_::inRandomOrder()->get(['id'])->first();
        $name = $this->faker->firstName;
        $newData = [
            'slug' => str_slug($name),
            'name_short' => $name,
            'slug_short' => str_slug($name)
        ];

        $this->call('patch', '/classes/' . $class->id, $newData);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('classes', $newData);
    }
}