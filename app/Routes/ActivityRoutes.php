<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("activities[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ActivityController@index");
});

$router->group(["prefix" => "activities"], function () use ($router) {
    $router->get("by-{what:[a-z\-]+}/{id:[1-9]\d*}/ids", "ActivityController@ids");
    $router->get("by-{what:[a-z\-]+}/{id:[1-9]\d*}", "ActivityController@by");
    $router->get("{key:[1-9]\d*}", "ActivityController@get");
    $router->get("{key:[a-z\-]+}/{value:[a-z0-9\-]+}", "ActivityController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->get("favorites", "ActivityController@favorites");
        $router->get("to-revise", "ActivityController@to_revise");
        $router->get("incomplete", "ActivityController@incomplete");

        $router->post("", "ActivityController@add");
        $router->patch("{id:[1-9]\d*}", "ActivityController@edit");
        $router->delete("{id:[1-9]\d*}", "ActivityController@delete");

        $router->post("search", "ActivityController@search");
        $router->patch("{id:[1-9]\d*}/mark", "ActivityController@mark");
    });
});
