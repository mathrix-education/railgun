<?php
/** @noinspection PhpRedundantCatchClauseInspection */

declare(strict_types=1);

namespace Tests\Integration\Facebook;

use App\Models\User;
use Exception;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Serializer\Serializer;
use Tests\APIStatefulTestCase;
use function app;
use function env;
use function json_decode;

/**
 * Test Facebook Login integration.
 */
class FacebookLoginTest extends APIStatefulTestCase
{
    private static $LimitReached;
    private static $FacebookUser;

    /**
     * Prepare Facebook test account
     *
     * @throws FacebookSDKException
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /** @var Facebook $facebook */
        $facebook = app()->make(Facebook::class);

        try {
            $response = $facebook->post(
                env('FACEBOOK_APP_ID') . '/accounts/test-users',
                [
                    'installed'   => true,
                    'permissions' => 'email',
                ],
                env('FACEBOOK_APP_TOKEN')
            );

            self::$FacebookUser = json_decode($response->getBody());
        } catch (FacebookResponseException $throttleException) {
            self::$LimitReached = true;
        }
    }

    /**
     * Cleanup of test campaign.
     *
     * @throws FacebookSDKException
     */
    public static function tearDownAfterClass(): void
    {
        if (self::$LimitReached) {
            return;
        }

        // Clean Facebook test account
        /** @var Facebook $facebook */
        $facebook = app()->make(Facebook::class);
        $response = $facebook->delete(self::$FacebookUser->id, [], env('FACEBOOK_APP_TOKEN'));
        $body     = json_decode($response->getBody());
        self::assertTrue($body->success);

        // Delete user (does not work for now)
        /** @var User $user */
        //$user = User::query()
        //    ->where('metadata->facebook->id', '=', self::$FacebookUser->id)
        //    ->firstOrFail();
        //$user->delete();

        parent::tearDownAfterClass();
    }

    /**
     * Handle Facebook Application request limit reached to skip the tests.
     */
    public function setUp(): void
    {
        if (self::$LimitReached) {
            $this->markTestSkipped('Facebook Application request limit reached, skipping');
        }

        parent::setUp();
    }

    /**
     * Make a Facebook login request using an access token
     *
     * @param string $accessToken The Facebook short-lived access token.
     *
     * @throws Exception
     */
    public function makeFacebookRequest(string $accessToken): void
    {
        $this->json('post', '/auth/login/facebook', ['access_token' => $accessToken]);

        $token      = json_decode($this->response->getContent())->data->token;
        $serializer = app()->make(Serializer::class);
        /** @var JWS $jws */
        $jws     = $serializer->unserialize($token);
        $payload = json_decode($jws->getPayload());
        /** @var User $user */
        $user = User::query()
            ->findOrFail($payload->sub);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
        $this->assertEquals(self::$FacebookUser->id, $user->metadata['facebook']['id']);
        $this->assertIsString($user->metadata['facebook']['access_token']);
        $this->assertIsInt($user->metadata['facebook']['expires_in']);
    }

    /**
     * POST /auth/facebook/login
     *
     * @throws Exception
     */
    public function testFacebookRegister()
    {
        $this->makeFacebookRequest(self::$FacebookUser->access_token);
    }

    /**
     * POST /auth/facebook/login
     *
     * This is exactly the same request, but we will check that there is only one account created.
     *
     * @throws Exception
     *
     * @depends testFacebookRegister
     */
    public function testFacebookLogin(): void
    {
        $this->makeFacebookRequest(self::$FacebookUser->access_token);

        /** @var User $user */
        $count = User::query()
            ->where('metadata->facebook->id', '=', self::$FacebookUser->id)
            ->count();

        $this->assertEquals(1, $count);
    }
}
