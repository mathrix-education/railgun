<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->get("trainings[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "TrainingController@index");

$router->group(["prefix" => "trainings", "middleware" => "auth:api"], function () use ($router) {
    $router->get("{key:[1-9]\d*}/recursive", "TrainingController@recursive");
    $router->post("{key:[1-9]\d*}/buy", "TrainingController@buy");

    $router->post("", "TrainingController@add");
    $router->patch("{id:[1-9]\d*}", "TrainingController@edit");
    $router->delete("{id:[1-9]\d*}", "TrainingController@delete");
});

$router->group(["prefix" => "trainings"], function () use ($router) {
    $router->get("[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "TrainingController@index");
    $router->get("{key:[1-9]\d*}", "TrainingController@get");
    $router->get("{key:[a-z\-]+}/{value:[a-z0-9\-]+}", "TrainingController@get");
});
