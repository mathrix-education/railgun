<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "categories"], function () use ($router) {
    $router->get("", "CategoryController@index");
    $router->get("{id:[0-9]+}", "CategoryController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "CategoryController@add");
        $router->patch("{id:[0-9]+}", "CategoryController@edit");
        $router->delete("{id:[0-9]+}", "CategoryController@delete");
    });
});