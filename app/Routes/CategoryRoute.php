<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->get("categories[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "CategoryController@index");

$router->group(["prefix" => "categories"], function () use ($router) {
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "CategoryController@add");
        $router->patch("{id:[1-9]\d*}", "CategoryController@edit");
        $router->delete("{id:[1-9]\d*}", "CategoryController@delete");
    });

    $router->get("{key:[1-9]\d*}", "CategoryController@get");
    $router->get("{key:[a-z\-]+}/{value:[a-z0-9\-]+}", "CategoryController@get");
});
