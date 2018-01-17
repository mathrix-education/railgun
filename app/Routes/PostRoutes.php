<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->get("posts[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "PostController@index");

$router->group(["prefix" => "posts"], function () use ($router) {
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "PostController@add");
        $router->patch("{id:[1-9]\d*}", "PostController@edit");
        $router->delete("{id:[1-9]\d*}", "PostController@delete");
    });

    $router->get("by-{what:[a-z\-]+}/{id:[1-9]\d*}", "PostController@by");
    $router->get("{key:[a-z\-]+}[/{value:[a-z0-9\-]+}]", "PostController@get");
});
