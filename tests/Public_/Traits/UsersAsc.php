<?php
namespace Tests\Public_\Traits;

use App\Models\Class_;
use App\Models\User;

/**
 * Trait UsersAsc
 * Test users as public
 *
 * @author Mathrix Team
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
     * Test GET /users/exists with existing username
     */
    public function testExistsSuccessUsernameExists()
    {
        /** @var User $user */
        $user = User::inRandomOrder()->whereNotNull('username')->first();

        $this->call('get', '/users/exists/username/' . $user->username);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeJson(['exists' => true]);
    }


    /**
     * Test GET /users/exists with non existing username
     */
    public function testExistsSuccessUsernameNotExist()
    {
        $this->call('get', '/users/exists/username/dummy-username');

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeJson(['exists' => false]);
    }


    /**
     * Test GET /users/exists with existing email
     */
    public function testExistsSuccessEmailExists()
    {
        /** @var User $user */
        $user = User::inRandomOrder()->first();

        $this->call('get', '/users/exists/email/' . $user->email);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeJson(['exists' => true]);
    }


    /**
     * Test GET /users/exists with non existing email
     */
    public function testExistsSuccessEmailNotExist()
    {
        $this->call('get', '/users/exists/email/dummy@email.zc');

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeJson(['exists' => false]);
    }
}