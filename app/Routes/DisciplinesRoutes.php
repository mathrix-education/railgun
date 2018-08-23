<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->get("disciplines[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "DisciplineController@index");

$router->group(["prefix" => "disciplines"], function () use ($router) {
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "DisciplineController@add");
        $router->patch("{id:[1-9]\d*}", "DisciplineController@edit");
        $router->delete("{id:[1-9]\d*}", "DisciplineController@delete");
    });

    $router->get("by-{what:[a-z\-]+}/{id:[1-9]\d*}", "DisciplineController@by");
    $router->get("by-{what:[a-z\-]+}/{id:[1-9]\d*}/ids", "DisciplineController@ids");
    $router->get("{key:[1-9]\d*}", "DisciplineController@get");
    $router->get("{key:[a-z\-]+}/{value:[a-z0-9\-]+}", "DisciplineController@get");
});
