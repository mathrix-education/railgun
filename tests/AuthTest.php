<?php
namespace Tests;

use App\Mails\User\PasswordForgotten;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthTest
 *
 * @author Mathrix Back-End Team
 * @package Tests
 * @since 0.8.1
 * @copyright Mathrix
 */
class AuthTest extends BaseTestCase
{
    // oAuth configuration
    const OAUTH_CLIENT_ID = 3;
    const OAUTH_CLIENT_SECRET = 'NCeWoR1cKpqEJQ6pW9gIOPXiH3iNCiV3f39fvyji';

    /** @var User $user */
    public $user;


    /**
     * Setup the test
     */
    public function setUp()
    {
        parent::setUp();

        // Ensure that the test user has been generated during the seeding process
        $this->user = User::inRandomOrder()->first();
    }


    /**
     * Expect HTTP 200 success
     */
    public function testLogin()
    {
        $this->call('post', '/users/login', $this->loginData());

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
            'refresh_token'
        ]);
        $this->assertLogged('user_logged', $this->user);

        return $this->response;
    }


    /*
    |--------------------------------------------------------------------------
    | POST /users/login
    |--------------------------------------------------------------------------
    */
    /**
     * Build login form data
     *
     * @return array
     */
    public function loginData(): array
    {
        return [
            'grant_type' => 'password',
            'client_id' => self::OAUTH_CLIENT_ID,
            'client_secret' => self::OAUTH_CLIENT_SECRET,
            'username' => $this->user->email,
            'password' => 'test'
        ];
    }


    /**
     * Expect HTTP 401 invalid_client
     */
    public function testLoginInvalidClient()
    {
        $data = $this->loginData();
        $data['client_secret'] .= 'wrong';

        $this->call('post', '/users/login', $data);

        // Assertions
        $this->assertJsonError('invalid_client', 401);
    }


    /**
     * Expect HTTP 401 unauthorized
     */
    public function testLoginInvalidCredentials()
    {
        $data = $this->loginData();
        $data['password'] = 'test2';

        $this->call('post', '/users/login', $data);

        // Assertions
        $this->assertJsonError('invalid_credentials', 401);
    }


    /**
     * Expect HTTP 200 success
     *
     * @depends testLogin
     *
     * @param Response $response
     */
    public function testRefresh(Response $response)
    {
        $this->call('post', '/users/refresh', [
            'grant_type' => 'refresh_token',
            'client_id' => self::OAUTH_CLIENT_ID,
            'client_secret' => self::OAUTH_CLIENT_SECRET,
            'refresh_token' => json_decode($response->getContent())->refresh_token
        ]);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
            'refresh_token'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | POST /users/refresh
    |--------------------------------------------------------------------------
    */


    /**
     * Expect HTTP 401 invalid_client
     *
     * @depends testLogin
     *
     * @param Response $response
     */
    public function testRefreshInvalidClient(Response $response)
    {

        $this->call('post', '/users/refresh', [
            'grant_type' => 'refresh_token',
            'client_id' => self::OAUTH_CLIENT_ID,
            'client_secret' => self::OAUTH_CLIENT_SECRET . 'wrong',
            'refresh_token' => json_decode($response->getContent())->refresh_token
        ]);

        // Assertions
        $this->assertJsonError('invalid_client', 401);
    }


    /**
     * Expect HTTP 400 invalid_request
     */
    public function testRefreshInvalidToken()
    {
        $this->call('post', '/users/refresh', [
            'grant_type' => 'refresh_token',
            'client_id' => self::OAUTH_CLIENT_ID,
            'client_secret' => self::OAUTH_CLIENT_SECRET,
            'refresh_token' => 'wrong_token'
        ]);

        // Assertions
        $this->assertJsonError('invalid_request', 401);
    }


    /**
     * Expect HTTP 200 success
     */
    public function testForgot()
    {
        Mail::fake();

        $this->call('post', '/users/forgot', ['email' => $this->user->email]);

        // Assertions
        $this->assertResponseStatus(200);
        Mail::assertSent(PasswordForgotten::class, function (PasswordForgotten $email) {
            return $email->hasTo($this->user->email);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | POST /users/forgot
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 404 model_not_found
     */
    public function testForgotModelNotFound()
    {
        Mail::fake();

        $this->call('post', '/users/forgot', ['email' => $this->user->email . '2']);

        // AssertionsS
        $this->assertJsonError('model_not_found', 404);
        Mail::assertNotSent(PasswordForgotten::class);
    }


    /**
     * Expect HTTP 200 success
     */
    public function testReset()
    {
        /** @var User $user */
        $user = User::whereNotNull('token')->first();

        $this->call('post', '/users/reset/' . $user->token, [
            'password' => 'test'
        ]);

        // Assertions
        $this->assertResponseStatus(200);
    }


    /*
    |--------------------------------------------------------------------------
    | POST /users/reset
    |--------------------------------------------------------------------------
    */


    /**
     * Expect HTTP 404 model_not_found
     */
    public function testResetModelNotFound()
    {
        $this->call('post', '/users/reset/foo', [
            'password' => 'test'
        ]);


        // Assertions
        $this->assertJsonError('model_not_found', 404);
    }


    /**
     * Expect HTTP 422 validation
     */
    public function testResetValidation()
    {
        $this->call('post', '/users/reset/foo');

        // Assertions
        $this->assertHttp400InvalidRequest();
    }


    protected function setUpTraits()
    {
        parent::setUpTraits();

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[DatabaseTransactions::class])) {
            $database = $this->app->make('db');

            $this->beforeApplicationDestroyed(function () use ($database) {
                foreach ($this->connectionsToTransact() as $name) {
                    $database->connection($name)->disconnect();
                }
            });
        }
    }
}