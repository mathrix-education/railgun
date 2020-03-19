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
 * @testdox [E2E] Mutation forgotPassword
 * @coversNothing
 */
class ForgotPasswordIntegration extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    private const QUERY = <<<'GRAPHQL'
    mutation($email: String!) {
        forgotPassword(email: $email)
    }
    GRAPHQL;

    /**
     * @testdox with non-existing email
     */
    public function testNonExistingEmail(): void
    {
        Mail::fake();

        $this->graphQL(self::QUERY, ['email' => 'idontexist@gmail.com']);

        $this->assertTrue($this->getData('forgotPassword'));
        Mail::assertNothingSent();
    }

    /**
     * @testdox with existing email
     */
    public function testExistingEmail(): void
    {
        Mail::fake();
        /** @var User $user */
        $user = factory(User::class)->create();

        $this->graphQL(self::QUERY, ['email' => $user->email]);

        $this->assertTrue($this->getData('forgotPassword'));
        Mail::assertSent(PasswordForgottenMail::class, fn(PasswordForgottenMail $mail) => $mail->hasTo($user->email));
    }

    /**
     * @testdox mailjet integration
     */
    public function testWithMailjet(): void
    {
        $this->app->make('config')->set('services.mailjet.call', false);
        /** @var User $user */
        $user = factory(User::class)->create();

        $this->graphQL(self::QUERY, ['email' => $user->email]);

        $this->assertTrue($this->getData('forgotPassword'));
    }
}
