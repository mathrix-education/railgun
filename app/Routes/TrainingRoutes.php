<?php
$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("trainings[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "TrainingController@index");
});

/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "trainings", "middleware" => "auth:api"], function () use ($router) {
    $router->get("[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "TrainingController@index");
    $router->get("{key:[a-z\-]+}[/{value:[a-z0-9\-]+}]", "TrainingController@get");
    $router->post("", "TrainingController@add");
    $router->patch("{id:[1-9]\d*}", "TrainingController@edit");
    $router->delete("{id:[1-9]\d*}", "TrainingController@delete");

    $router->get("{key:[a-z\-]+}[/{value:[a-z0-9\-]+}]", "TrainingController@get");
});
