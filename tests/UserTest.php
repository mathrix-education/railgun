<?php

use App\Models\User;

class UserTest extends TestCase
{
    const CLIENT_ID = 3;

    private $_user;
    private $_client;

    /**
     * Get a user
     * @return mixed
     */
    private function _user()
    {
        if (!isset($this->_user)) {
            $this->_user = User::all()->first();
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
                ->where('id', '=', self::CLIENT_ID)
                ->first();
        }

        return $this->_client;
    }


    /**
     * @param string $username
     * @param string $password
     *
     * @return \Illuminate\Http\Response
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
        $this->seeJsonEquals([
            'error' => 'invalid_credentials',
            'message' => 'The user credentials were incorrect.'
        ]);
        $this->assertResponseStatus(401);
    }

    /**
     * Test user not found
     */
    public function testLoginUserNotFound()
    {
        $this->_tryLogin('test@test.fr.com');
        $this->seeJsonEquals([
            'error' => 'not_found',
            'message' => 'User not found'
        ]);
        $this->assertResponseStatus(404);
    }

    /**
     * Test user register ok
     */
    public function testRegisterOk()
    {
        $faker = Faker\Factory::create(); // Create a faker
        $this->call('POST', '/user/register', [
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

    public function testRegisterValidationFails()
    {

    }
}