<?php
/** @var Laravel\Lumen\Routing\Router $router */

$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("discounts[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "DiscountController@index");
});

$router->group(["prefix" => "discounts"], function () use ($router) {
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("check", "DiscountController@check");
        $router->post("", "DiscountController@add");
        $router->get("{key:[1-9]\d*}", "DiscountController@get");
        $router->get("{key:[a-z\-]+}/{value:[a-z0-9\-]+}", "DiscountController@get");
        $router->patch("{id:[1-9]\d*}", "DiscountController@edit");
        $router->delete("{id:[1-9]\d*}", "DiscountController@delete");
    });
});
