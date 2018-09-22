<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("purchases[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "PurchaseController@index");

    $router->group(["prefix" => "purchases"], function () use ($router) {
        $router->post("", "PurchaseController@add");
        $router->post("buy/{purchasableType}/{purchasableId:[1-9]\d*}", "PurchaseController@buy");
        $router->get("{key:[1-9]\d*}", "PurchaseController@get");
        $router->patch("{id:[1-9]\d*}", "PurchaseController@edit");
        $router->delete("{id:[1-9]\d*}", "PurchaseController@delete");
    });
});
