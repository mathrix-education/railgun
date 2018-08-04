<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "transactions", "middleware" => "auth:api"], function () use ($router) {
    $router->post("buy", "TransactionController@buy");
    $router->post("sale", "TransactionController@sale");
    $router->get("{transactionId:[a-z0-9\_]+}", "TransactionController@find");
    $router->post("{transactionId:[a-z0-9\_]+}/void", "TransactionController@void");
    $router->post("{transactionId:[a-z0-9\_]+}/refund", "TransactionController@refund");
});
