<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "disciplines"], function () use ($router) {
    $router->get("", "DisciplineController@index");
    $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "DisciplinesController@index");
    $router->get("{id:[0-9]+}", "DisciplineController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "DisciplineController@add");
        $router->patch("{id:[0-9]+}", "DisciplineController@edit");
        $router->delete("{id:[0-9]+}", "DisciplineController@delete");
    });
});

