<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->get("products[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ProductController@index");

$router->group(["prefix" => "products"], function () use ($router) {
    $router->get("salable", "ProductController@salable");

    $router->get("{key:[1-9]\d*}", "ProductController@get");
    $router->get("{key:[a-z\-]+}/{value:[a-z0-9\-]+}", "ProductController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("buy", "ProductController@buy");
        $router->post("sale", "ProductController@sale");

        $router->post("", "ProductController@add");
        $router->patch("{id:[1-9]\d*}", "ProductController@edit");
        $router->delete("{id:[1-9]\d*}", "ProductController@delete");
    });
});
