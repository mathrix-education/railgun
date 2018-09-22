<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->post("braintree/webhook", "BraintreeController@webhook");

$router->group(["prefix" => "braintree", "middleware" => "auth:api"], function () use ($router) {
    $router->get("client-token", "BraintreeController@clientToken");
});
