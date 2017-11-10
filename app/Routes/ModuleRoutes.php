<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "modules"], function () use ($router) {
    $router->get("", "ModuleController@index");
    $router->get("{id:[0-9]+}", "ModuleController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "ModuleController@add");
        $router->patch("{id:[0-9]+}", "ModuleController@edit");
        $router->delete("{id:[0-9]+}", "ModuleController@delete");
    });
});