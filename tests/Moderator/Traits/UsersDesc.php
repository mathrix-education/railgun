<?php
namespace Tests\Moderator\Traits;

use App\Models\Class_;
use App\Models\User;

trait UsersDesc
{
    /*
    |--------------------------------------------------------------------------
    | POST /users/register
    |--------------------------------------------------------------------------
    */
    /**
     * Test POST /users/register with 401 unauthorized
     */
    public function testRegisterAdminUnauthorized()
    {
        $this->assertCannotRegister('admin');
    }


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
     * Test POST /users/register with 401 unauthorized
     */
    public function testRegisterModeratorUnauthorized()
    {
        $this->assertCannotRegister('moderator');
    }


    /**
     * Test POST /users/register with 401 unauthorized
     */
    public function testRegisterPremiumUnauthorized()
    {
        $this->assertCannotRegister('premium');
    }


    /**
     * Test GET /users/{id} for stranger admin
     */
    public function testGetStrangerAdmin()
    {
        $this->assertCannotGetStranger('admin');
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
     * Test GET /users/{id} for stranger moderator
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
     * Test PATCH /users/{id} with 401 unauthorized
     */
    public function testEditAdminUnauthorized()
    {
        $this->assertCannotEdit('admin');
    }


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
     * Test PATCH /users/{id} with 401 unauthorized
     */
    public function testEditModeratorUnauthorized()
    {
        $this->assertCannotEdit('moderator');
    }


    /**
     * Test PATCH /users/{id} with 401 unauthorized
     */
    public function testEditPremiumUnauthorized()
    {
        $this->assertCannotEdit('premium');
    }


    /**
     * Test PATCH /users/{id} with 401 unauthorized
     */
    public function testEditRegisteredUnauthorized()
    {
        $this->assertCannotEdit('registered');
    }
}