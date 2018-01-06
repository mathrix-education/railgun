<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->get("posts[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "PostController@index");

$router->group(["prefix" => "posts"], function () use ($router) {
    $router->get("[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "PostController@index");
    $router->get("{id:[1-9]\d*}", "PostController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "PostController@add");
        $router->patch("{id:[1-9]\d*}", "PostController@edit");
        $router->delete("{id:[1-9]\d*}", "PostController@delete");
    });
});
