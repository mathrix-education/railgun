<?php

use App\Models\User;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;

/**
 * Class UserTest
 * Test UserController methods
 *
 * @see \App\Http\Controllers\UserController
 */
class UserTest extends BaseTest
{
    /*
    |--------------------------------------------------------------------------
    | Common
    |--------------------------------------------------------------------------
    |
    | Common variables and methods
    |
    */
    /**
     * @var User|null
     */
    private $_user;
    /**
     * @var stdClass|null
     */
    private $_client;

    /**
     * Get a user
     * @return User|null
     */
    private function _user()
    {
        if (!isset($this->_user)) {
            $this->_user = User::where('id', '<', 50) // Be sure to get a seed user with 'test' as password
                ->inRandomOrder()
                ->first();
        }

        return $this->_user;
    }

    /**
     * Get client
     * @return stdClass
     */
    private function _client()
    {
        if (!isset($this->_client)) {
            $this->_client = DB::table('oauth_clients')
                ->where('id', '=', env('CLIENT_ID'))
                ->first();
        }

        return $this->_client;
    }


    /**
     * @param string $username
     * @param string $password
     *
     * @return Response
     */
    private function _tryLogin(string $username, string $password = 'test')
    {
        return $this->call('POST', '/users/login', [
            'grant_type' => 'password',
            'client_id' => $this->_client()->id,
            'client_secret' => $this->_client()->secret,
            'username' => $username,
            'password' => $password
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Tests
    |--------------------------------------------------------------------------
    |
    | User tests
    |
    */
    /**
     * Test user login ok
     */
    public function testLoginOk()
    {
        $this->_tryLogin($this->_user()->email);
        $this->seeJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
            'refresh_token'
        ]);
        $this->assertResponseOk();
    }

    /**
     * Test user login with bad credentials
     */
    public function testLoginBadCredentials()
    {
        $this->_tryLogin($this->_user()->email, 'badpassword');
        $this->assertResponseStatus(401);
    }

    /**
     * Test user not found
     */
    public function testLoginUserNotFound()
    {
        $this->_tryLogin('test@test.fr.com');
        $this->assertResponseStatus(404);
    }

    /**
     * Test token refreshing
     */
    public function testRefreshOk()
    {
        $response = $this->_tryLogin($this->_user()->email);
        $tokens = json_decode($response->getContent());

        $this->call('post', '/users/refresh', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $tokens->refresh_token,
            'client_id' => $this->_client()->id,
            'client_secret' => $this->_client()->secret,
        ]);

        $this->assertResponseOk();
    }

    /**
     * Test token refresh with invalid token
     */
    public function testRefreshInvalidToken()
    {
        $this->call('post', '/users/refresh', [
            'grant_type' => 'refresh_token',
            'refresh_token' => 'foo',
            'client_id' => $this->_client()->id,
            'client_secret' => $this->_client()->secret,
        ]);

        $this->seeJsonEquals([
            'error' => 'invalid_request',
            'message' => 'The refresh token is invalid.',
            'hint' => 'Cannot decrypt the refresh token'
        ]);
        $this->assertResponseStatus(400);
    }

    /**
     * Test user register ok
     */
    public function testRegisterOk()
    {
        $faker = Faker\Factory::create(); // Create a faker
        $this->call('POST', '/users/register', [
            'email' => $faker->email,
            'password' => 'test',
            'class_id' => $faker->randomElement(
                \App\Models\Class_::all()
                    ->pluck('id')
                    ->toArray()
            )
        ]);
        $this->assertResponseOk();
    }

    /**
     * Test user register with missing field
     */
    public function testRegisterMissingField()
    {
        $faker = Faker\Factory::create(); // Create a faker

        $data = [
            'email' => $faker->email,
            'password' => 'test',
            'class_id' => $faker->randomElement(
                \App\Models\Class_::all()
                    ->pluck('id')
                    ->toArray()
            )
        ];

        // Pick a random data field...
        $dataKeys = array_keys($data);
        shuffle($dataKeys);
        unset($data[$dataKeys[0]]); // .. and delete it

        $this->call('POST', '/users/register', $data);
        $this->assertResponseStatus(422);
    }

    /**
     * Test user register with bad data
     */
    public function testRegisterBadData()
    {
        $this->call('POST', '/users/register', [
            'email' => 'hsjdknvsdlkqs',
            'password' => '5',
            'class_id' => 'qsf5q4s4d4qzf'
        ]);
        $this->seeJsonStructure([
            'error',
            'message'
        ]);
        $this->assertResponseStatus(422);
    }

    /**
     * Test user profile retrieving
     */
    public function testProfileOk()
    {
        Passport::actingAs($this->_user());
        $this->call('GET', '/users');
        $this->assertResponseOk();
    }

    /**
     * Test user profile retrieving without authorization
     */
    public function testProfileNotAuthorized()
    {
        $this->call('GET', '/users');
        $this->assertResponseStatus(401);
    }

    /**
     * Test other user profile retrieving
     */
    public function testProfileOtherUser()
    {
        $otherId = User::inRandomOrder()->first()->id;

        Passport::actingAs($this->_user());
        $this->call('GET', '/users/' . $otherId);
        $this->assertResponseOk();
    }

    /**
     * Test user profile retrieving when user does not exist
     */
    public function testProfileNotFound()
    {
        Passport::actingAs($this->_user());
        $this->call('GET', '/users/123456789');
        $this->assertResponseStatus(404);
    }

    /**
     * Test user account modification with password change
     */
    public function testAccountOkWithPasswordChange()
    {
        Passport::actingAs($this->_user());
        $newSettings = factory(User::class)->make()->toArray();
        unset($newSettings['status'], $newSettings['points'], $newSettings['facebook_token']);
        $newSettings['new_password'] = 'test';
        $newSettings['old_password'] = 'test';

        $this->call('POST', '/users', $newSettings);

        $this->assertResponseOk();
    }

    /**
     * Test user account modification without password
     */
    public function testAccountOkWithoutPasswordChange()
    {
        Passport::actingAs($this->_user());
        $newSettings = factory(User::class)->make()->toArray();
        unset($newSettings['status'], $newSettings['points'], $newSettings['facebook_token']);

        $this->call('POST', '/users', $newSettings);

        $this->assertResponseOk();
    }

    /**
     * Test user account modification with invalid old password
     */
    public function testAccountInvalidOldPassword() {
        Passport::actingAs($this->_user());
        $newSettings = factory(User::class)->make()->toArray();
        unset($newSettings['status'], $newSettings['points'], $newSettings['facebook_token']);
        $newSettings['old_password'] = 'test2';
        $newSettings['new_password'] = 'test';

        $this->call('POST', '/users', $newSettings);

        $this->assertResponseStatus(401);
    }

    /**
     * Test user account modification with invalid old password
     */
    public function testAccountInvalidData() {
        Passport::actingAs($this->_user());
        $newSettings = factory(User::class)->make()->toArray();
        unset($newSettings['status'], $newSettings['points'], $newSettings['facebook_token']);
        $newSettings['email'] = 'lol';

        $this->call('POST', '/users', $newSettings);

        $this->assertResponseStatus(422);
    }
}