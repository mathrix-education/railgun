<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->get("disciplines[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "DisciplineController@index");

$router->group(["prefix" => "disciplines"], function () use ($router) {
    $router->get("{id:[1-9]\d*}", "DisciplineController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "DisciplineController@add");
        $router->patch("{id:[1-9]\d*}", "DisciplineController@edit");
        $router->delete("{id:[1-9]\d*}", "DisciplineController@delete");
    });
});
