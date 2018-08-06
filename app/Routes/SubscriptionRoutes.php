<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "subscriptions"], function () use ($router) {
    $router->get("estimate/{plan}", "SubscriptionController@estimate");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "SubscriptionController@create");
        $router->get("{subscriptionId:[a-z0-9\_]+}", "SubscriptionController@find");
        $router->post("{subscriptionId:[a-z0-9\_]+}/cancel", "SubscriptionController@cancel");
        $router->get("by-user/{userId}", "SubscriptionController@byUser");
    });
});
