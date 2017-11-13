<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "classes"], function () use ($router) {
    $router->get("", "ClassController@index");
    $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "ClassController@index");
    $router->get("{id:[0-9]+}", "ClassController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "ClassController@add");
        $router->patch("{id:[0-9]+}", "ClassController@edit");
        $router->delete("{id:[0-9]+}", "ClassController@delete");
    });
});
