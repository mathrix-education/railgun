<?php
/** @var Laravel\Lumen\Routing\Router $router */

use App\Mails\PasswordForgottenMail;
use App\Mails\PasswordResetMail;
use App\Models\User;

// @codeCoverageIgnoreStart
if (app()->environment("dev")) {
    $router->group(["prefix" => "mails"], function () use ($router) {
        $router->get("password-forgotten", function () {
            $user = User::inRandomOrder()->first();
            return new PasswordForgottenMail($user);
        });
        $router->get("password-reset", function () {
            $user = User::inRandomOrder()->first();
            return new PasswordResetMail($user);
        });
    });
}
// @codeCoverageIgnoreEnd
