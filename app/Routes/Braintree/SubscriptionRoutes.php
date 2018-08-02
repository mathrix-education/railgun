<?php
/** @var Laravel\Lumen\Routing\Router $router */

$router->group(["middleware" => "auth:api", "prefix" => "subscriptions"], function () use ($router) {
    $router->post("", "SubscriptionController@create");
});
