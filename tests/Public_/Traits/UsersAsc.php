<?php
namespace Tests\Public_\Traits;

use App\Models\Class_;
use App\Models\User;

/**
 * Trait UsersAsc
 * Test users as public
 *
 * @package Tests\Public_\Traits
 */
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
    public function testRegister()
    {
        $this->call('post', '/users/register', [
            'email' => $this->faker->email,
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
    public function testRegisterWithoutType()
    {
        $this->call('post', '/users/register', [
            'email' => $this->faker->email,
            'password' => 'test',
            'class_id' => Class_::inRandomOrder()->get(['id'])->pluck('id')->first()
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure($this->jsonStructure);
    }

    /*
    |--------------------------------------------------------------------------
    | /users/exists
    |--------------------------------------------------------------------------
    */
    /**
     * Test GET /users/exists with existing email
     */
    public function testExistsSuccessEmailExists()
    {
        $user = User::inRandomOrder()->first();

        $this->call('get', '/users/exists/' . $user->email);
        $this->assertResponseStatus(200);
        $this->seeJson(['exists' => true]);
    }

    /**
     * Test GET /users/exists with non existing email
     */
    public function testExistsSuccessEmailNotExist()
    {
        $this->call('get', '/users/exists/dummy@email.zc');
        $this->assertResponseStatus(200);
        $this->seeJson(['exists' => false]);
    }
}