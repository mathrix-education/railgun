<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->get("classes[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ClassController@index");
$router->group(["prefix" => "classes"], function () use ($router) {
    $router->get("{id:[1-9]\d*}", "ClassController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "ClassController@add");
        $router->patch("{id:[1-9]\d*}", "ClassController@edit");
        $router->delete("{id:[1-9]\d*}", "ClassController@delete");
    });
});

