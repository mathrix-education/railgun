<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "transactions", "middleware" => "auth:api"], function () use ($router) {
    $router->get("by-subscription/{subscriptionId:[a-z0-9\_]+}", "TransactionController@bySubscription");
    $router->get("by-user/{userId:[a-z0-9\_]+}", "TransactionController@byUser");

    $router->get("{transactionId:[a-z0-9\_]+}", "TransactionController@find");
    $router->post("{transactionId:[a-z0-9\_]+}/void", "TransactionController@void");
    $router->post("{transactionId:[a-z0-9\_]+}/refund", "TransactionController@refund");
});
