<?php

namespace Tests\Integration\Facebook;

use App\Models\User;
use Exception;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Illuminate\Contracts\Container\BindingResolutionException;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Serializer\Serializer;
use Tests\APIStatefulTestCase;
use function app;
use function env;
use function json_decode;

/**
 * Class AuthLoginFacebook.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.1.1-rc1
 */
class FacebookLoginTest extends APIStatefulTestCase
{
    private static $FacebookUser;

    /**
     * Prepare Facebook test account
     *
     * @throws FacebookSDKException
     * @throws BindingResolutionException
     */
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        /** @var Facebook $facebook */
        $facebook = app()->make(Facebook::class);

        $response = $facebook->post(env('FACEBOOK_APP_ID') . '/accounts/test-users', [
            'installed' => true,
            'permissions' => 'email',
        ], env('FACEBOOK_APP_TOKEN'));

        self::$FacebookUser = json_decode($response->getBody());
    }


    /**
     * Cleanup of test campaign.
     *
     * @throws FacebookSDKException
     * @throws BindingResolutionException
     */
    public static function tearDownAfterClass(): void
    {
        // Clean Facebook test account
        /** @var Facebook $facebook */
        $facebook = app()->make(Facebook::class);
        $response = $facebook->delete(self::$FacebookUser->id, [], env('FACEBOOK_APP_TOKEN'));
        $body = json_decode($response->getBody());
        self::assertTrue($body->success);

        // Delete user (does not work for now)
        /** @var User $user */
        //$user = User::query()
        //    ->where('metadata->facebook->id', '=', self::$FacebookUser->id)
        //    ->firstOrFail();
        //$user->logs()->delete();
        //$user->delete();

        parent::tearDownAfterClass();
    }


    /**
     * POST /auth/facebook/login
     */
    public function testFacebookRegister()
    {
        $this->makeFacebookRequest(self::$FacebookUser->access_token);
    }


    /**
     * Make a Facebook login request using an access token
     *
     * @param string $accessToken
     */
    public function makeFacebookRequest(string $accessToken)
    {
        $this->json('post', '/auth/login/facebook', [
            'access_token' => $accessToken
        ]);

        $token = json_decode($this->response->getContent())->token;
        $serializer = app()->make(Serializer::class);
        /** @var JWS $jws */
        $jws = $serializer->unserialize($token);
        $payload = json_decode($jws->getPayload());
        /** @var User $user */
        $user = User::query()->findOrFail($payload->sub);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
        $this->assertEquals(self::$FacebookUser->id, $user->metadata['facebook']['id']);
        $this->assertIsString($user->metadata['facebook']['access_token']);
        $this->assertIsInt($user->metadata['facebook']['expires_in']);
    }


    /**
     * POST /auth/facebook/login
     *
     * This is exactly the same request, but we will check that there is only one account created.
     *
     * @depends testFacebookRegister
     * @throws Exception
     */
    public function testFacebookLogin()
    {
        $this->makeFacebookRequest(self::$FacebookUser->access_token);

        /** @var User $user */
        $count = User::query()
            ->where('metadata->facebook->id', '=', self::$FacebookUser->id)
            ->count();

        $this->assertEquals(1, $count);
    }
}
