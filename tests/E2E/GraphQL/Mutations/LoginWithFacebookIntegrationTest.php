<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\GraphNodes\GraphUser;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function app;
use function env;

/**
 * @testdox Mutation loginWithFacebook
 */
class LoginWithFacebookIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    private const QUERY = <<<'GRAPHQL'
    mutation ($accessToken: String!) {
        loginWithFacebook(accessToken: $accessToken) {
            token
        }
    }
    GRAPHQL;

    private static bool      $LimitReached = false;
    private static GraphUser $FacebookUser;

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
        $response = $facebook->delete(self::$FacebookUser->getId(), [], env('FACEBOOK_APP_TOKEN'));
        self::assertFalse($response->isError());

        parent::tearDownAfterClass();
    }

    /**
     * Handle Facebook Application request limit reached to skip the tests.
     *
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function setUp(): void
    {
        parent::setUp();

        if (self::$LimitReached) {
            $this->markTestSkipped('Facebook Application request limit reached, skipping');
        }

        $facebook = app()->make(Facebook::class);

        try {
            $response = $facebook->post(
                env('FACEBOOK_APP_ID') . '/accounts/test-users',
                [
                    'installed'   => true,
                    'permissions' => 'email',
                ],
                env('FACEBOOK_APP_TOKEN'),
            );

            self::$FacebookUser = $response->getGraphUser();
        } catch (FacebookResponseException | FacebookSDKException $facebookException) {
            self::$LimitReached = true;
            $this->markTestSkipped('Facebook Application request limit reached, skipping');
        }
    }

    /**
     * @testdox register a new user and returns a JWT
     */
    public function testFacebookRegister(): void
    {
        $this->graphQL(self::QUERY, [
            'accessToken' => self::$FacebookUser->getField('access_token'),
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals(1, User::query()->where([
            ['email', '=', self::$FacebookUser->getEmail()],
            ['metadata->facebook->id', '=', self::$FacebookUser->getId()],
        ])->count());
        $this->assertNotEmpty($this->getContent('data.loginWithFacebook.token'));
    }
}
