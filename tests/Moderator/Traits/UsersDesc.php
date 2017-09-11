<?php
namespace Tests\Moderator\Traits;

use App\Models\Class_;
use App\Models\User;

/**
 * Trait UsersDesc
 * Test users as moderator or lower
 *
 * @package Tests\Moderator\Traits
 */
trait UsersDesc
{
    /*
    |--------------------------------------------------------------------------
    | POST /users/register
    |--------------------------------------------------------------------------
    */
    /**
     * Assert user cannot register a user with the given type
     *
     * @param string $type
     */
    public function assertCannotRegister(string $type)
    {
        $this->call('post', '/users/register', [
            'email' => $this->faker->email,
            'password' => 'test',
            'type' => $type,
            'class_id' => in_array($type, ['registered, premium']) ? Class_::get(['id'])->pluck('id')->first() : null
        ]);

        // Assertions
        $this->assertJsonError('unauthorized', 401);
    }


    /**
     * Try to register an admin
     * Expect HTTP 401 unauthorized
     */
    public function testRegisterAdminUnauthorized()
    {
        $this->assertCannotRegister('admin');
    }


    /**
     * Try to register an moderator
     * Expect HTTP 401 unauthorized
     */
    public function testRegisterModeratorUnauthorized()
    {
        $this->assertCannotRegister('moderator');
    }


    /**
     * Try to register a premium user
     * Expect HTTP 401 unauthorized
     */
    public function testRegisterPremiumUnauthorized()
    {
        $this->assertCannotRegister('premium');
    }


    /*
    |--------------------------------------------------------------------------
    | GET /users/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Assert user cannot get a stranger
     *
     * @param string $type
     */
    public function assertCannotGetStranger(string $type)
    {
        $stranger = $this->getStranger(['type', '=', $type]);

        $this->call('get', '/users/' . $stranger->id);

        // Assertions
        $this->assertJsonError('unauthorized', 401);
    }


    /**
     * Get stranger admin
     * Expect HTTP 401 unauthorized
     */
    public function testGetStrangerAdmin()
    {
        $this->assertCannotGetStranger('admin');
    }


    /**
     * Get stranger moderator
     * Expect HTTP 401 unauthorized
     */
    public function testGetStrangerModerator()
    {
        $this->assertCannotGetStranger('moderator');
    }


    /*
    |--------------------------------------------------------------------------
    | PATCH /users/{id}
    |--------------------------------------------------------------------------
    */
    /**
     * Assert user cannot edit a user with the given type
     *
     * @param string $type
     */
    public function assertCannotEdit(string $type)
    {
        $where = [['type', '=', $type]];
        if (isset($this->user)) {
            $where[] = ['id', '!=', $this->user->id];
        }

        $strangerAdmin = User::inRandomOrder()->where($where)->first()->toArray();

        $this->call('patch', '/users/' . $strangerAdmin['id'], $strangerAdmin);

        // Assertions
        $this->assertJsonError('unauthorized', 401);
    }


    /**
     * Try to edit an admin
     * Expect HTTP 401 unauthorized
     */
    public function testEditAdminUnauthorized()
    {
        $this->assertCannotEdit('admin');
    }


    /**
     * Try to edit an moderator
     * Expect HTTP 401 unauthorized
     */
    public function testEditModeratorUnauthorized()
    {
        $this->assertCannotEdit('moderator');
    }


    /**
     * Try to edit a premium user
     * Expect HTTP 401 unauthorized
     */
    public function testEditPremiumUnauthorized()
    {
        $this->assertCannotEdit('premium');
    }


    /**
     * Try to edit a registered user
     * Expect HTTP 401 unauthorized
     */
    public function testEditRegisteredUnauthorized()
    {
        $this->assertCannotEdit('registered');
    }
}