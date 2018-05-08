<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "stripe"], function () use ($router) {
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("subscribe", "StripeController@subscribe");
    });

    $router->get("estimate/{stripe_plan}", "StripeController@estimate");
    $router->post("webhook", "StripeController@handleWebhook");
});
