<?php
namespace Tests\Public_\Traits;

use App\Models\Class_;
use Faker\Factory;

trait UsersAsc
{
    public $jsonStructure = [
        'id',
        'email',
        'settings' => [
            'daily_goal',
            'sound',
            'email_newsletter',
            'email_comment',
        ],
        'class_id'
    ];

    /**
     * Test POST /users/register
     */
    public function testRegisterSuccess()
    {
        $faker = Factory::create();

        $this->call('post', '/users/register', [
            'email' => $faker->email,
            'password' => 'test',
            'type' => 'register',
            'class_id' => Class_::inRandomOrder()->get(['id'])->pluck('id')->first()
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure($this->jsonStructure);
    }

    /**
     * Test POST /users/register without type
     */
    public function testRegisterSuccessWithoutType()
    {
        $faker = Factory::create();

        $this->call('post', '/users/register', [
            'email' => $faker->email,
            'password' => 'test',
            'class_id' => Class_::inRandomOrder()->get(['id'])->pluck('id')->first()
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure($this->jsonStructure);
    }
}