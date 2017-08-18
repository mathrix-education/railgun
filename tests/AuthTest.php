<?php
namespace Tests;

use App\Models\User;
use Faker\Factory;
use Mathrix\Lumen\Tests\BaseTestCase;

/**
 * Class AuthTest
 * Test auth actions
 * @author Mathrix Team
 * @package Tests
 */
class AuthTest extends BaseTestCase
{
    /** @var User $user */
    public $user;

    public function setUp()
    {
        parent::setUp();

        // Ensure that the test user has been generated during the seeding process
        $this->user = User::where('id', '<', 100)
            ->whereNotNull('email')
            ->inRandomOrder()
            ->first();
    }

    /**
     * Build login data from existing user
     * @return array
     */
    public function loginData()
    {
        return [
            'grant_type' => 'password',
            'client_id' => env('TEST_OAUTH_CLIENT_ID'),
            'client_secret' => env('TEST_OAUTH_CLIENT_SECRET'),
            'username' => $this->user->email,
            'password' => 'test'
        ];
    }

    /**
     * Build refresh data from login
     */
    public function refreshData()
    {
        $this->call('post', '/users/login', $this->loginData());

        return [
            'grant_type' => 'refresh',
            'client_id' => env('TEST_OAUTH_CLIENT_ID'),
            'client_secret' => env('TEST_OAUTH_CLIENT_SECRET'),
            'refresh_token' => json_decode($this->response->getContent())->refresh_token
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | POST /users/login
    |--------------------------------------------------------------------------
    */
    /**
     * Test POST /users/login with 200 success
     */
    public function testLoginSuccess()
    {
        $this->call('post', '/users/login', $this->loginData());

        $this->seeJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
            'refresh_token'
        ]);

        $this->assertResponseStatus(200);
    }

    /**
     * Test POST /users/login with 401 invalid_credentials
     */
    public function testLoginInvalidCredentials()
    {
        $data = $this->loginData();
        $data['password'] = 'test2';
        $this->call('post', '/users/login', $data);

        $this->assertJsonError('invalid_credentials', 401);
    }

    /**
     * Test POST /users/login with 404 model_not_found
     */
    public function testLoginModelNotFound()
    {
        $data = $this->loginData();
        $data['username'] .= 'x';
        $this->call('post', '/users/login', $data);

        $this->assertJsonError('model_not_found', 404);
    }

    /**
     * Test POST /users/login with 422 validation
     */
    public function testLoginValidation()
    {
        $data = $this->loginData();
        $keyToDelete = Factory::create()->randomElement(array_keys($data));
        unset($data[$keyToDelete]);

        $this->call('post', '/users/login', $data);

        $this->assertJsonError('validation', 422);
    }


    /*
    |--------------------------------------------------------------------------
    | POST /users/refresh
    |--------------------------------------------------------------------------
    */
    /**
     * Test POST /users/refresh with 200 success
     */
    public function testRefreshSuccess()
    {
        $this->call('post', '/users/refresh', $this->refreshData());

        $this->seeJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
            'refresh_token'
        ]);
        $this->assertResponseStatus(200);
    }

    /**
     * Test POST /users/refresh with 400 invalid_request
     */
    public function testRefreshInvalidRequest()
    {
        $this->call('post', '/users/refresh', [
            'grant_type' => 'refresh_token',
            'client_id' => env('TEST_OAUTH_CLIENT_ID'),
            'client_secret' => env('TEST_OAUTH_CLIENT_SECRET'),
            'refresh_token' => 'foo'
        ]);

        $this->assertJsonError('invalid_request', 401);
    }

    /**
     * Test POST /users/login with 422 validation
     */
    public function testRefreshValidation()
    {
        $data = $this->loginData();
        $keyToDelete = Factory::create()->randomElement(array_keys($data));
        unset($data[$keyToDelete]);

        $this->call('post', '/users/login', $data);

        $this->assertJsonError('validation', 422);
    }


    /*
    |--------------------------------------------------------------------------
    | POST /users/forgot
    |--------------------------------------------------------------------------
    */
    /**
     * Test POST /users/forgot with 200 success
     */
    public function testForgotSuccess()
    {
        $this->call('post', '/users/forgot', ['email' => $this->user->email]);

        $this->assertResponseStatus(200);
    }

    /**
     * Test POST /users/forgot with 404 model_not_found
     */
    public function testForgotModelNotFound()
    {
        $this->call('post', '/users/forgot', ['email' => $this->user->email . '2']);

        $this->assertJsonError('model_not_found', 404);
    }


    /*
    |--------------------------------------------------------------------------
    | POST /users/reset
    |--------------------------------------------------------------------------
    */
    /**
     * Test POST /users/reset with 200 success
     */
    public function testResetSuccess()
    {
        /**
         * @var User $user
         */
        $user = User::whereNotNull('token')->first();

        $this->call('post', '/users/reset/' . $user->token, [
            'password' => 'test'
        ]);

        $this->assertResponseStatus(200);
    }

    /**
     * Test POST /users/reset with 404 model_not_found
     */
    public function testResetModelNotFound()
    {
        $this->call('post', '/users/reset/foo', [
            'password' => 'test'
        ]);

        $this->assertJsonError('model_not_found', 404);
    }

    /**
     * Test POST /users/reset with 422 validation
     */
    public function testResetValidation()
    {
        $this->call('post', '/users/reset/foo');

        $this->assertJsonError('validation', 422);
    }
}