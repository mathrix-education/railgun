<?php

namespace Tests\Users;

use App\Mails\PasswordForgottenMail;
use App\Models\Class_;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\ScopeTestCase;

/**
 * Class UsersAsPublicTest
 * Test users as public user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix SA
 * @version 1.0.0
 * @package Tests\Quotes
 */
class UsersAsPublicTest extends ScopeTestCase
{
    /**
     * POST /users/register
     */
    public function testRegister()
    {
        $data = [
            "email" => $this->faker->email,
            "password" => "test",
            "class_id" => Class_::inRandomOrder()->firstOrFail()->id
        ];
        $dataWithoutPassword = $data;
        unset($dataWithoutPassword["password"]);

        $this->call("post", "/users/register", $data);

        $user = User::whereEmail($data["email"])->firstOrFail();

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase("users", $dataWithoutPassword);
        $this->assertInDatabase("logs", [
            "event" => "user_registered",
            "user_id" => $user->id
        ]);
    }

    /**
     * POST /users/forgot
     */
    public function testForgot()
    {
        Mail::fake();

        $email = User::inRandomOrder()->first()->email;
        $this->call("POST", "/users/forgot", ["email" => $email]);

        $this->assertHttp200Success();
        Mail::assertSent(PasswordForgottenMail::class, function (PasswordForgottenMail $mail) use ($email) {
            return $mail->hasTo($email);
        });
    }
}
