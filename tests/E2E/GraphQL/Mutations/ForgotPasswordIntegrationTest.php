<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Facades\Mail;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Mails\PasswordForgottenMail;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * @testdox Mutation forgotPassword
 */
class ForgotPasswordIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    private const QUERY = <<<'GRAPHQL'
    mutation($email: String!) {
        forgotPassword(email: $email)
    }
    GRAPHQL;

    /**
     * @testdox do nothing using a non-existing email
     */
    public function testNonExistingEmail(): void
    {
        Mail::fake();

        $this->graphQL(self::QUERY, ['email' => 'idontexist@gmail.com']);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertTrue($this->getData('forgotPassword'));
        Mail::assertNothingSent();
    }

    /**
     * @testdox sends email using an existing email
     */
    public function testExistingEmail(): void
    {
        Mail::fake();
        /** @var User $user */
        $user = factory(User::class)->create();

        $this->graphQL(self::QUERY, ['email' => $user->email]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertTrue($this->getData('forgotPassword'));
        Mail::assertSent(
            PasswordForgottenMail::class,
            static fn(PasswordForgottenMail $mail) => $mail->hasTo($user->email),
        );
    }

    /**
     * @testdox uses mailjet integration
     */
    public function testWithMailjet(): void
    {
        $this->app->make('config')->set('services.mailjet.call', false);
        /** @var User $user */
        $user = factory(User::class)->create();

        $this->graphQL(self::QUERY, ['email' => $user->email]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertTrue($this->getData('forgotPassword'));
    }
}
