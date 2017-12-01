<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "trainings"], function () use ($router) {
    $router->get("", "TrainingController@index");
    $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "TrainingController@index");
    $router->get("{id:[0-9]+}", "TrainingController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "TrainingController@add");
        $router->patch("{id:[0-9]+}", "TrainingController@edit");
        $router->delete("{id:[0-9]+}", "TrainingController@delete");
    });
});
