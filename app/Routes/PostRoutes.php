<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "posts"], function () use ($router) {
    $router->get("", "PostController@index");
    $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "PostController@index");
    $router->get("{id:[0-9]+}", "PostController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "PostController@add");
        $router->patch("{id:[0-9]+}", "PostController@edit");
        $router->delete("{id:[0-9]+}", "PostController@delete");
    });
});